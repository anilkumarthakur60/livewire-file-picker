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

    public static function plankNotInstalled(): self
    {
        return new self(
            'The plank/laravel-mediable package is required to use the "plank" driver. '
            .'Install it via: composer require plank/laravel-mediable'
        );
    }
}
