<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Exceptions;

use Illuminate\Database\Eloquent\Model;

class DuplicateMediaException extends FilePickerException
{
    public ?Model $existing = null;

    public static function withHash(string $hash, ?Model $existing = null): self
    {
        $instance = new self("Duplicate file detected (hash: {$hash}).");
        $instance->existing = $existing;

        return $instance;
    }
}
