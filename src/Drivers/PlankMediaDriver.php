<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaMovedToFolder;
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
use Plank\Mediable\Media;
use Plank\Mediable\MediaUploader;
use Throwable;

/**
 * Driver built on top of plank/laravel-mediable.
 *
 * Wraps Plank's `Media` model (via the `FilePickerMedia` subclass) with extra
 * columns (folder, tags, favorites, hash, etc.) supplied by an additive
 * migration.
 */
final class PlankMediaDriver extends AbstractDriver
{
    /** @var class-string<Media> */
    private readonly string $model;

    private readonly string $disk;

    private readonly string $directory;

    private readonly string $visibility;

    public function __construct(MediaTransformerInterface $transformer)
    {
        parent::__construct($transformer);

        /** @var class-string<Media> $model */
        $model = config('file-picker.drivers.plank.model', FilePickerMedia::class);
        $this->model = $model;

        /** @var string $disk */
        $disk = config('file-picker.drivers.plank.disk', 'public');
        $this->disk = $disk;

        /** @var string $directory */
        $directory = config('file-picker.drivers.plank.directory', 'media');
        $this->directory = $directory;

        /** @var string $visibility */
        $visibility = config('file-picker.drivers.plank.visibility', 'public');
        $this->visibility = $visibility;
    }

    public function driverName(): string
    {
        return 'plank';
    }

    /**
     * @param array<string, mixed> $options
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

        $folder = isset($options['folder']) && is_string($options['folder']) && $options['folder'] !== ''
            ? trim($options['folder'])
            : null;

        $directory = $folder !== null
            ? rtrim($this->directory, '/') . '/' . trim($folder, '/')
            : $this->directory;

        $tags = $this->resolveTags($options);
        $alt = isset($options['alt']) && is_string($options['alt']) ? $options['alt'] : null;
        $userId = $this->resolveUserId($options);

        try {
            $uploader = $this->uploader();
            $uploader
                ->setModelClass($this->model)
                ->fromSource($file)
                ->toDestination($this->disk, $directory)
                ->useFilename($filename)
                ->onDuplicateIncrement();

            if ($this->visibility === 'public') {
                $uploader->makePublic();
            } else {
                $uploader->makePrivate();
            }

            $uploader->beforeSave(function (Media $model) use ($hash, $folder, $tags, $alt, $userId): void {
                if ($hash !== '') {
                    $model->setAttribute('hash', $hash);
                }
                $model->setAttribute('folder', $folder);
                $model->setAttribute('tags', $tags);
                if ($alt !== null) {
                    $model->setAttribute('alt', $alt);
                }
                $model->setAttribute('user_id', $userId);
                $model->setAttribute('is_favorite', false);
            });

            /** @var Media $media */
            $media = $uploader->upload();

            $this->backfillImageDimensions($media);

            MediaUploaded::dispatch($media, $this->driverName());

            return $media;
        } catch (Throwable $e) {
            if ($e instanceof DuplicateMediaException || $e instanceof StorageQuotaExceededException) {
                throw $e;
            }

            throw UploadFailedException::fromPrevious($originalName, $e);
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function replaceFile(int $id, TemporaryUploadedFile $file, array $options = []): Model
    {
        /** @var Media $existing */
        $existing = $this->findByIdOrFail($id);

        $oldPath = $existing->getDiskPath();
        $hash = $this->computeHash($file);

        try {
            $uploader = $this->uploader();
            $uploader
                ->setModelClass($this->model)
                ->fromSource($file);

            if (($options['keep_filename'] ?? false) === true) {
                $existingFilename = $existing->getAttribute('filename');
                $uploader->useFilename(is_string($existingFilename) ? $existingFilename : '');
            } else {
                $originalName = $file->getClientOriginalName();
                $uploader->useFilename(pathinfo($originalName, PATHINFO_FILENAME));
            }

            $uploader->beforeSave(function (Media $model) use ($hash): void {
                if ($hash !== '') {
                    $model->setAttribute('hash', $hash);
                }
                $model->setAttribute('width', null);
                $model->setAttribute('height', null);
            });

            /** @var Media $media */
            $media = $uploader->replace($existing);

            $this->backfillImageDimensions($media);

            $newPath = $media->getDiskPath();

            MediaReplaced::dispatch($media, $oldPath, $newPath, $this->driverName());

            return $media;
        } catch (Throwable $e) {
            throw UploadFailedException::fromPrevious($file->getClientOriginalName(), $e);
        }
    }

    public function moveToFolder(int $id, ?string $folder): bool
    {
        /** @var Media $media */
        $media = $this->findByIdOrFail($id);

        $oldFolder = $media->getAttribute('folder');
        $oldFolderString = is_string($oldFolder) ? $oldFolder : null;

        $newFolder = $folder !== null && trim($folder) !== '' ? trim($folder) : null;

        if ($oldFolderString === $newFolder) {
            return false;
        }

        $newDirectory = $newFolder !== null
            ? rtrim($this->directory, '/') . '/' . trim($newFolder, '/')
            : $this->directory;

        $currentDirectory = is_string($media->getAttribute('directory'))
            ? $media->getAttribute('directory')
            : '';

        if ($currentDirectory !== $newDirectory) {
            $diskName = is_string($media->getAttribute('disk')) ? $media->getAttribute('disk') : $this->disk;

            if (Storage::disk($diskName)->exists($media->getDiskPath())) {
                $media->move($newDirectory);
            } else {
                $media->setAttribute('directory', $newDirectory);
            }
        }

        $media->setAttribute('folder', $newFolder);
        $media->save();

        MediaMovedToFolder::dispatch($id, $oldFolderString, $newFolder, $this->driverName());

        return true;
    }

    protected function modelClass(): string
    {
        return $this->model;
    }

    protected function performDelete(Model $media): void
    {
        $media->forceDelete();
    }

    private function uploader(): MediaUploader
    {
        /** @var MediaUploader $uploader */
        $uploader = app('mediable.uploader');

        return $uploader;
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
     * @param array<string, mixed> $options
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
     * @param array<string, mixed> $options
     *
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
     * @param array<string, mixed> $options
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

    private function backfillImageDimensions(Media $media): void
    {
        $mimeType = is_string($media->getAttribute('mime_type')) ? $media->getAttribute('mime_type') : '';

        if (! str_starts_with($mimeType, 'image/') || $mimeType === 'image/svg+xml') {
            return;
        }

        $disk = is_string($media->getAttribute('disk')) ? $media->getAttribute('disk') : $this->disk;

        /** @var FilesystemAdapter $diskInstance */
        $diskInstance = Storage::disk($disk);

        $fullPath = $diskInstance->path($media->getDiskPath());

        if (! file_exists($fullPath)) {
            return;
        }

        $imageSize = @getimagesize($fullPath);

        if ($imageSize === false) {
            return;
        }

        $media->setAttribute('width', $imageSize[0]);
        $media->setAttribute('height', $imageSize[1]);
        $media->save();
    }
}
