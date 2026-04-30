<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Support;

use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;

/**
 * Default authorization that allows everything.
 *
 * Replace this with your own implementation via the config key
 * 'authorization_class' to enforce per-user access control.
 */
final class DefaultAuthorization implements FilePickerAuthorizationInterface
{
    public function canUpload(): bool
    {
        return true;
    }

    public function canDelete(int $mediaId): bool
    {
        return true;
    }

    public function canViewLibrary(): bool
    {
        return true;
    }

    public function canEditAlt(int $mediaId): bool
    {
        return true;
    }

    public function canRestore(int $mediaId): bool
    {
        return true;
    }

    public function canForceDelete(int $mediaId): bool
    {
        return true;
    }

    public function canReplace(int $mediaId): bool
    {
        return true;
    }

    public function canFavorite(int $mediaId): bool
    {
        return true;
    }

    public function canTag(int $mediaId): bool
    {
        return true;
    }

    public function canMove(int $mediaId): bool
    {
        return true;
    }

    public function canDownload(int $mediaId): bool
    {
        return true;
    }
}
