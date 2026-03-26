<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaUploaded;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;
use Anil\LivewireFilePicker\Models\FilePickerMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class DefaultDriver extends AbstractDriver
{
    /** @var class-string<Model> */
    private readonly string $model;

    private readonly string $disk;

    private readonly string $directory;

    private readonly string $visibility;

    public function __construct(MediaTransformerInterface $transformer)
    {
        parent::__construct($transformer);

        /** @var class-string<Model> $model */
        $model = config('file-picker.drivers.default.model', FilePickerMedia::class);
        $this->model = $model;

        /** @var string $disk */
        $disk = config('file-picker.drivers.default.disk', 'public');
        $this->disk = $disk;

        /** @var string $directory */
        $directory = config('file-picker.drivers.default.directory', 'media');
        $this->directory = $directory;

        /** @var string $visibility */
        $visibility = config('file-picker.drivers.default.visibility', 'public');
        $this->visibility = $visibility;
    }

    public function driverName(): string
    {
        return 'default';
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function upload(TemporaryUploadedFile $file, array $options = []): Model
    {
        $originalName = $file->getClientOriginalName();

        /** @var string $filename */
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $size = $file->getSize();

        $storedPath = $file->storeAs(
            $this->directory,
            $filename.'_'.uniqid().'.'.$extension,
            [
                'disk' => $this->disk,
                'visibility' => $this->visibility,
            ]
        );

        if ($storedPath === false) {
            throw UploadFailedException::storageFailed($originalName);
        }

        /** @var array<string, mixed> $attributes */
        $attributes = [
            'filename' => $filename,
            'disk' => $this->disk,
            'directory' => $this->directory,
            'path' => $storedPath,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $size,
            'alt' => $options['alt'] ?? null,
        ];

        $this->extractImageDimensions($mimeType, $storedPath, $attributes);

        /** @var Model $media */
        $media = $this->modelClass()::query()->create($attributes);

        MediaUploaded::dispatch($media, $this->driverName());

        return $media;
    }

    protected function modelClass(): string
    {
        return $this->model;
    }

    protected function performDelete(Model $media): void
    {
        /** @var string $path */
        $path = $media->getAttribute('path') ?? '';

        /** @var string $disk */
        $disk = $media->getAttribute('disk') ?? $this->disk;

        if ($path !== '' && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        $media->delete();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function extractImageDimensions(string $mimeType, string $storedPath, array &$attributes): void
    {
        if (! str_starts_with($mimeType, 'image/') || $mimeType === 'image/svg+xml') {
            return;
        }

        /** @var FilesystemAdapter $diskInstance */
        $diskInstance = Storage::disk($this->disk);
        $fullPath = $diskInstance->path($storedPath);

        if (! file_exists($fullPath)) {
            return;
        }

        $imageSize = @getimagesize($fullPath);

        if ($imageSize !== false) {
            $attributes['width'] = $imageSize[0];
            $attributes['height'] = $imageSize[1];
        }
    }
}
