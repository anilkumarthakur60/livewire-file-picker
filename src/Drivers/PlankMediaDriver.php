<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaReplaced;
use Anil\LivewireFilePicker\Events\MediaUploaded;
use Anil\LivewireFilePicker\Exceptions\DriverNotFoundException;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Plank\Mediable\MediaUploader;
use Throwable;

/**
 * Driver adapter for plank/laravel-mediable.
 *
 * Plank is an optional dependency. The stub file at stubs/PlankMediable.stub.php
 * provides type information for PHPStan when plank is not installed.
 */
final class PlankMediaDriver extends AbstractDriver
{
    private const PLANK_MEDIA_CLASS = 'Plank\Mediable\Media';

    /** @var class-string<Model> */
    private readonly string $model;

    private readonly string $disk;

    private readonly string $directory;

    private readonly string $visibility;

    public function __construct(MediaTransformerInterface $transformer)
    {
        parent::__construct($transformer);
        if (! class_exists(self::PLANK_MEDIA_CLASS)) {
            throw DriverNotFoundException::plankNotInstalled();
        }
        /** @var class-string<Model> $model */
        $model = config('file-picker.drivers.plank.model', self::PLANK_MEDIA_CLASS);
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
     * @param  array<string, mixed>  $options
     */
    public function upload(TemporaryUploadedFile $file, array $options = []): Model
    {
        $originalName = $file->getClientOriginalName();
        try {
            /** @var string $filename */
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            /** @var MediaUploader $uploader */
            $uploader = app('mediable.uploader');
            $configured = $uploader
                ->fromSource($file)
                ->toDestination($this->disk, $this->directory)
                ->useFilename($filename)
                ->onDuplicateIncrement();
            if ($this->visibility === 'public') {
                $configured->makePublic();
            } else {
                $configured->makePrivate();
            }
            /** @var Model $media */
            $media = $configured->upload();
            MediaUploaded::dispatch($media, $this->driverName());

            return $media;
        } catch (Throwable $e) {
            throw UploadFailedException::fromPrevious($originalName, $e);
        }
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function replaceFile(int $id, TemporaryUploadedFile $file, array $options = []): Model
    {
        $existing = $this->findByIdOrFail($id);
        $oldPath = $this->buildPlankPath($existing);
        $replacement = $this->upload($file, $options);
        $existing->delete();
        $newPath = $this->buildPlankPath($replacement);
        MediaReplaced::dispatch($replacement, $oldPath, $newPath, $this->driverName());

        return $replacement;
    }

    public function getFolders(): array
    {
        return [];
    }

    public function getAllTags(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        return [
            'total_count' => $this->query()->count(),
            'total_size' => 0,
            'favorites_count' => 0,
            'trashed_count' => 0,
            'folders_count' => 0,
            'tags_count' => 0,
            'by_type' => [],
        ];
    }

    public function findByHash(string $hash): ?Model
    {
        return null;
    }

    public function updateAlt(int $id, string $alt): bool
    {
        return false;
    }

    public function toggleFavorite(int $id): bool
    {
        return false;
    }

    public function setFavorite(int $id, bool $favorite): bool
    {
        return false;
    }

    /**
     * @param  array<int, string>  $tags
     */
    public function setTags(int $id, array $tags): bool
    {
        return false;
    }

    public function addTag(int $id, string $tag): bool
    {
        return false;
    }

    public function removeTag(int $id, string $tag): bool
    {
        return false;
    }

    public function moveToFolder(int $id, ?string $folder): bool
    {
        return false;
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkMoveToFolder(array $ids, ?string $folder): int
    {
        return 0;
    }

    public function incrementDownloadCount(int $id): void {}

    protected function modelClass(): string
    {
        return $this->model;
    }

    protected function performDelete(Model $media): void
    {
        $media->delete();
    }

    private function buildPlankPath(Model $media): string
    {
        $directory = $media->getAttribute('directory');
        $filename = $media->getAttribute('filename');
        if (! is_string($directory) || ! is_string($filename)) {
            return '';
        }

        return rtrim($directory, '/').'/'.$filename;
    }
}
