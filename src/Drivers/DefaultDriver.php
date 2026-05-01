<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaReplaced;
use Anil\LivewireFilePicker\Events\MediaRestored;
use Anil\LivewireFilePicker\Events\MediaUploaded;
use Anil\LivewireFilePicker\Exceptions\DuplicateMediaException;
use Anil\LivewireFilePicker\Exceptions\StorageQuotaExceededException;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;
use Anil\LivewireFilePicker\Models\FilePickerMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Auth;
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
        $size = (int) $file->getSize();

        $this->ensureQuotaNotExceeded($size, $options);

        $hash = $this->computeHash($file);

        if ($hash !== '' && (bool) config('file-picker.duplicate_detection.enabled', true)) {
            $existing = $this->findByHash($hash);

            if ($existing !== null) {
                /** @var string $strategy */
                $strategy = config('file-picker.duplicate_detection.strategy', 'reuse');

                $isTrashed = method_exists($existing, 'trashed') && $existing->trashed();

                if ($strategy === 'reuse') {
                    if ($isTrashed && method_exists($existing, 'restore')) {
                        $existing->restore();
                        MediaRestored::dispatch($existing, $this->driverName());
                    }

                    return $existing;
                }

                if ($strategy === 'reject') {
                    throw DuplicateMediaException::withHash($hash, $existing);
                }
                // strategy === 'allow' falls through and creates a new record
            }
        }

        /** @var string $filename */
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $folder = isset($options['folder']) && is_string($options['folder']) && $options['folder'] !== ''
            ? trim($options['folder'])
            : null;

        $directory = $folder !== null
            ? rtrim($this->directory, '/').'/'.trim($folder, '/')
            : $this->directory;

        $storedPath = $file->storeAs(
            $directory,
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
            'directory' => $directory,
            'path' => $storedPath,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $size,
            'hash' => $hash !== '' ? $hash : null,
            'folder' => $folder,
            'alt' => $options['alt'] ?? null,
            'user_id' => $this->resolveUserId($options),
            'tags' => $this->resolveTags($options),
        ];

        $this->extractImageDimensions($mimeType, $storedPath, $attributes);

        /** @var Model $media */
        $media = $this->modelClass()::query()->create($attributes);

        MediaUploaded::dispatch($media, $this->driverName());

        return $media;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function replaceFile(int $id, TemporaryUploadedFile $file, array $options = []): Model
    {
        $media = $this->findByIdOrFail($id);

        $oldPath = is_string($media->getAttribute('path')) ? $media->getAttribute('path') : '';
        $oldDisk = is_string($media->getAttribute('disk')) ? $media->getAttribute('disk') : $this->disk;

        $originalName = $file->getClientOriginalName();
        /** @var string $filename */
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $size = (int) $file->getSize();

        $directory = is_string($media->getAttribute('directory'))
            ? $media->getAttribute('directory')
            : $this->directory;

        $newStoredPath = $file->storeAs(
            $directory,
            $filename.'_'.uniqid().'.'.$extension,
            [
                'disk' => $this->disk,
                'visibility' => $this->visibility,
            ]
        );

        if ($newStoredPath === false) {
            throw UploadFailedException::storageFailed($originalName);
        }

        $hash = $this->computeHash($file);

        $attributes = [
            'path' => $newStoredPath,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'size' => $size,
            'hash' => $hash !== '' ? $hash : null,
            'width' => null,
            'height' => null,
        ];

        if (($options['keep_filename'] ?? false) === false) {
            $attributes['filename'] = $filename;
        }

        $this->extractImageDimensions($mimeType, $newStoredPath, $attributes);

        foreach ($attributes as $key => $value) {
            $media->setAttribute($key, $value);
        }

        $media->save();

        if ($oldPath !== '' && $oldPath !== $newStoredPath && Storage::disk($oldDisk)->exists($oldPath)) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        MediaReplaced::dispatch($media, $oldPath, $newStoredPath, $this->driverName());

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

        $media->forceDelete();
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

    private function computeHash(TemporaryUploadedFile $file): string
    {
        $path = $file->getRealPath();

        if ($path === '' || ! is_file($path)) {
            return '';
        }

        $hash = @hash_file('sha256', $path);

        return is_string($hash) ? $hash : '';
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function resolveUserId(array $options): ?int
    {
        if (array_key_exists('user_id', $options)) {
            return is_numeric($options['user_id']) ? (int) $options['user_id'] : null;
        }

        if (! (bool) config('file-picker.ownership.auto_assign', true)) {
            return null;
        }

        if (! Auth::check()) {
            return null;
        }

        $id = Auth::id();

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<int, string>|null
     */
    private function resolveTags(array $options): ?array
    {
        if (! isset($options['tags']) || ! is_array($options['tags'])) {
            return null;
        }

        $clean = [];

        foreach ($options['tags'] as $t) {
            if (is_string($t) && trim($t) !== '') {
                $clean[] = trim($t);
            }
        }

        return $clean === [] ? null : array_values(array_unique($clean));
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws StorageQuotaExceededException
     */
    private function ensureQuotaNotExceeded(int $incomingSize, array $options): void
    {
        /** @var int $globalQuota */
        $globalQuota = config('file-picker.storage_quota.global', 0);
        /** @var int $userQuota */
        $userQuota = config('file-picker.storage_quota.per_user', 0);

        if ($globalQuota > 0) {
            $used = (int) $this->queryWithTrashed()->sum('size');

            if ($used + $incomingSize > $globalQuota) {
                throw StorageQuotaExceededException::global($used + $incomingSize, $globalQuota);
            }
        }

        if ($userQuota > 0) {
            $userId = $this->resolveUserId($options);

            if ($userId !== null) {
                $used = (int) $this->queryWithTrashed()->where('user_id', $userId)->sum('size');

                if ($used + $incomingSize > $userQuota) {
                    throw StorageQuotaExceededException::forUser($userId, $used + $incomingSize, $userQuota);
                }
            }
        }
    }
}
