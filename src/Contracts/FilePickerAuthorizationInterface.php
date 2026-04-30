<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Contracts;

/**
 * Authorization contract for the file picker.
 *
 * Implement this interface to control who can upload, delete, and
 * view media items. Register your implementation in the config
 * via 'authorization_class'.
 */
interface FilePickerAuthorizationInterface
{
    /**
     * Determine if the current user can upload files.
     */
    public function canUpload(): bool;

    /**
     * Determine if the current user can delete a specific media item.
     */
    public function canDelete(int $mediaId): bool;

    /**
     * Determine if the current user can view the media library.
     */
    public function canViewLibrary(): bool;

    /**
     * Determine if the current user can edit alt text.
     */
    public function canEditAlt(int $mediaId): bool;

    /**
     * Determine if the current user can restore a trashed media item.
     */
    public function canRestore(int $mediaId): bool;

    /**
     * Determine if the current user can permanently delete a media item.
     */
    public function canForceDelete(int $mediaId): bool;

    /**
     * Determine if the current user can replace the underlying file of a media item.
     */
    public function canReplace(int $mediaId): bool;

    /**
     * Determine if the current user can favorite or unfavorite a media item.
     */
    public function canFavorite(int $mediaId): bool;

    /**
     * Determine if the current user can manage tags on a media item.
     */
    public function canTag(int $mediaId): bool;

    /**
     * Determine if the current user can move a media item between folders.
     */
    public function canMove(int $mediaId): bool;

    /**
     * Determine if the current user can download a media item.
     */
    public function canDownload(int $mediaId): bool;
}
