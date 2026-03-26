<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Exceptions;

class MediaNotFoundException extends FilePickerException
{
    public static function withId(int $id): self
    {
        return new self("Media item not found with ID: {$id}");
    }
}
