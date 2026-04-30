<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Exceptions;

class StorageQuotaExceededException extends FilePickerException
{
    public static function forUser(int $userId, int $usedBytes, int $quotaBytes): self
    {
        return new self(
            "Storage quota exceeded for user {$userId}: {$usedBytes} bytes used of {$quotaBytes} allowed."
        );
    }

    public static function global(int $usedBytes, int $quotaBytes): self
    {
        return new self(
            "Global storage quota exceeded: {$usedBytes} bytes used of {$quotaBytes} allowed."
        );
    }
}
