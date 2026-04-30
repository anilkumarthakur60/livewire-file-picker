<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Contracts;

use Anil\LivewireFilePicker\Exceptions\DuplicateMediaException;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Exceptions\StorageQuotaExceededException;
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
     * Get a query builder including soft-deleted (trashed) items.
     *
     * @return Builder<Model>
     */
    public function queryWithTrashed(): Builder;

    /**
     * Get a query builder for trashed items only.
     *
     * @return Builder<Model>
     */
    public function queryOnlyTrashed(): Builder;

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
     * Find a media item by its content hash.
     */
    public function findByHash(string $hash): ?Model;

    /**
     * Upload a file and create a media record.
     *
     * @param  array<string, mixed>  $options
     *
     * @throws UploadFailedException
     * @throws DuplicateMediaException
     * @throws StorageQuotaExceededException
     */
    public function upload(TemporaryUploadedFile $file, array $options = []): Model;

    /**
     * Replace the underlying file of an existing media record.
     *
     * @param  array<string, mixed>  $options
     *
     * @throws MediaNotFoundException
     * @throws UploadFailedException
     */
    public function replaceFile(int $id, TemporaryUploadedFile $file, array $options = []): Model;

    /**
     * Soft-delete (trash) a media item by ID.
     *
     * @throws MediaNotFoundException
     */
    public function delete(int $id): bool;

    /**
     * Restore a previously trashed media item by ID.
     *
     * @throws MediaNotFoundException
     */
    public function restore(int $id): bool;

    /**
     * Permanently delete a trashed (or live) media item, removing the file from disk.
     *
     * @throws MediaNotFoundException
     */
    public function forceDelete(int $id): bool;

    /**
     * Soft-delete multiple media items by IDs.
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
     * Rename a media item (changes the human filename, not the path on disk).
     *
     * @throws MediaNotFoundException
     */
    public function rename(int $id, string $newFilename): bool;

    /**
     * Toggle the favorite status of a media item.
     *
     * @throws MediaNotFoundException
     */
    public function toggleFavorite(int $id): bool;

    /**
     * Set the favorite status of a media item to a specific value.
     *
     * @throws MediaNotFoundException
     */
    public function setFavorite(int $id, bool $favorite): bool;

    /**
     * Replace the entire tag list on a media item.
     *
     * @param  array<int, string>  $tags
     *
     * @throws MediaNotFoundException
     */
    public function setTags(int $id, array $tags): bool;

    /**
     * Add a single tag to a media item (no-op if already present).
     *
     * @throws MediaNotFoundException
     */
    public function addTag(int $id, string $tag): bool;

    /**
     * Remove a single tag from a media item.
     *
     * @throws MediaNotFoundException
     */
    public function removeTag(int $id, string $tag): bool;

    /**
     * Move a media item into a folder (pass null to move to root).
     *
     * @throws MediaNotFoundException
     */
    public function moveToFolder(int $id, ?string $folder): bool;

    /**
     * Move multiple media items into a folder.
     *
     * @param  array<int>  $ids
     */
    public function bulkMoveToFolder(array $ids, ?string $folder): int;

    /**
     * Increment the download counter for analytics.
     */
    public function incrementDownloadCount(int $id): void;

    /**
     * Get the list of distinct folders currently in use.
     *
     * @return array<int, string>
     */
    public function getFolders(): array;

    /**
     * Get the list of all distinct tags currently in use.
     *
     * @return array<int, string>
     */
    public function getAllTags(): array;

    /**
     * Get aggregate statistics about the media library.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array;

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
