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
}
