<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Contracts;

use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

interface MediaDriverInterface
{
    /**
     * Get the driver name identifier.
     */
    public function driverName(): string;

    /**
     * Get a new query builder for media items.
     *
     * @return Builder<Model>
     */
    public function query(): Builder;

    /**
     * Find a media item by its ID.
     */
    public function findById(int $id): ?Model;

    /**
     * Find a media item by its ID or throw.
     *
     * @throws MediaNotFoundException
     */
    public function findByIdOrFail(int $id): Model;

    /**
     * Find media items by an array of IDs (preserving order).
     *
     * @param  array<int>  $ids
     * @return Collection<int, Model>
     */
    public function findByIds(array $ids): Collection;

    /**
     * Upload a file and create a media record.
     *
     * @param  array<string, mixed>  $options
     *
     * @throws UploadFailedException
     */
    public function upload(TemporaryUploadedFile $file, array $options = []): Model;

    /**
     * Delete a media item by ID.
     *
     * @throws MediaNotFoundException
     */
    public function delete(int $id): bool;

    /**
     * Delete multiple media items by IDs.
     *
     * @param  array<int>  $ids
     * @return int Number of successfully deleted items.
     */
    public function deleteMany(array $ids): int;

    /**
     * Update the alt text of a media item.
     *
     * @throws MediaNotFoundException
     */
    public function updateAlt(int $id, string $alt): bool;

    /**
     * Rename a media item.
     *
     * @throws MediaNotFoundException
     */
    public function rename(int $id, string $newFilename): bool;

    /**
     * Check if a media item exists.
     */
    public function exists(int $id): bool;

    /**
     * Transform a Model into a normalized array for the UI.
     *
     * @return array<string, mixed>
     */
    public function transform(Model $media): array;
}
