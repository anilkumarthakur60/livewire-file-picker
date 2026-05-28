<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Http\Controllers;

use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;
use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Events\MediaDownloaded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final class MediaDownloadController
{
    public function __construct(
        private readonly MediaDriverInterface $driver,
        private readonly FilePickerAuthorizationInterface $authorization,
    ) {}

    public function download(int $id): StreamedResponse
    {
        if (! (bool) config('file-picker.features.download', true)) {
            abort(403, 'Downloads are disabled.');
        }

        if (! $this->authorization->canDownload($id)) {
            abort(403);
        }

        $media = $this->driver->findById($id);

        if ($media === null) {
            abort(404);
        }

        $disk = $this->getDiskName($media);
        $path = $this->getPath($media);
        $filename = $this->getDownloadFilename($media);

        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $this->driver->incrementDownloadCount($id);
        MediaDownloaded::dispatch($id, $this->driver->driverName());

        return Storage::disk($disk)->download($path, $filename);
    }

    public function downloadZip(Request $request): Response
    {
        if (! (bool) config('file-picker.features.bulk_download', true)) {
            abort(403, 'Bulk downloads are disabled.');
        }

        /** @var array<int, int|string>|null $idsParam */
        $idsParam = $request->input('ids');

        if (! is_array($idsParam) || $idsParam === []) {
            abort(400, 'No ids provided.');
        }

        $ids = array_values(array_filter(array_map(intval(...), $idsParam), fn (int $id): bool => $id > 0));

        if ($ids === []) {
            abort(400, 'No valid ids.');
        }

        foreach ($ids as $id) {
            if (! $this->authorization->canDownload($id)) {
                abort(403);
            }
        }

        if (! class_exists(ZipArchive::class)) {
            abort(500, 'ZipArchive extension is not available.');
        }

        $items = $this->driver->findByIds($ids);

        if ($items->isEmpty()) {
            abort(404);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'fp-zip-');

        if ($tempPath === false) {
            abort(500, 'Could not create temp zip file.');
        }

        $zip = new ZipArchive;

        if ($zip->open($tempPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($tempPath);
            abort(500, 'Could not open zip archive for writing.');
        }

        $usedNames = [];

        foreach ($items as $media) {
            $disk = $this->getDiskName($media);
            $path = $this->getPath($media);

            if ($path === '' || ! Storage::disk($disk)->exists($path)) {
                continue;
            }

            $entryName = $this->uniqueZipEntryName($this->getDownloadFilename($media), $usedNames);
            $usedNames[$entryName] = true;

            $contents = Storage::disk($disk)->get($path);

            if (is_string($contents)) {
                $zip->addFromString($entryName, $contents);
            }

            $key = $media->getKey();
            if (is_int($key) || is_numeric($key)) {
                $intKey = (int) $key;
                $this->driver->incrementDownloadCount($intKey);
                MediaDownloaded::dispatch($intKey, $this->driver->driverName());
            }
        }

        $zip->close();

        $downloadName = 'media-' . date('Ymd-His') . '.zip';

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function getDiskName(Model $media): string
    {
        $disk = $media->getAttribute('disk');

        if (is_string($disk) && $disk !== '') {
            return $disk;
        }

        /** @var string $configured */
        $configured = config('file-picker.drivers.plank.disk', 'public');

        return $configured;
    }

    private function getPath(Model $media): string
    {
        if (method_exists($media, 'getDiskPath')) {
            $path = $media->getDiskPath();

            return is_string($path) ? $path : '';
        }

        $directory = $media->getAttribute('directory');
        $filename = $media->getAttribute('filename');
        $extension = $media->getAttribute('extension');

        if (! is_string($directory) || ! is_string($filename) || $filename === '') {
            return '';
        }

        $ext = is_string($extension) && $extension !== '' ? '.' . $extension : '';

        return ltrim(rtrim($directory, '/') . '/' . $filename . $ext, '/');
    }

    private function getDownloadFilename(Model $media): string
    {
        $name = $media->getAttribute('filename');
        $ext = $media->getAttribute('extension');

        $base = is_string($name) && $name !== '' ? $name : 'file';
        $extString = is_string($ext) && $ext !== '' ? '.' . $ext : '';

        return $base . $extString;
    }

    /**
     * @param array<string, true> $usedNames
     */
    private function uniqueZipEntryName(string $name, array $usedNames): string
    {
        if (! isset($usedNames[$name])) {
            return $name;
        }

        $info = pathinfo($name);
        $stem = $info['filename'] !== '' ? $info['filename'] : 'file';
        $ext = isset($info['extension']) && $info['extension'] !== '' ? '.' . $info['extension'] : '';

        $i = 1;

        while (isset($usedNames[$stem . '-' . $i . $ext])) {
            $i++;
        }

        return $stem . '-' . $i . $ext;
    }
}
