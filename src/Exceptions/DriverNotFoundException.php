<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Exceptions;

class DriverNotFoundException extends FilePickerException
{
    public static function forDriver(string $driver): self
    {
        return new self(
            "File picker driver [{$driver}] is not a valid class or does not implement MediaDriverInterface."
        );
    }
}
