<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Exceptions;

class UploadFailedException extends FilePickerException
{
    public static function storageFailed(string $filename): self
    {
        return new self("Failed to store the uploaded file: {$filename}");
    }

    public static function fromPrevious(string $filename, \Throwable $previous): self
    {
        return new self(
            message: "Upload failed for file: {$filename} - {$previous->getMessage()}",
            previous: $previous,
        );
    }
}
