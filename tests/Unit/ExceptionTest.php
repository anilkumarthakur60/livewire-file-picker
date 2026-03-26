<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Exceptions\DriverNotFoundException;
use Anil\LivewireFilePicker\Exceptions\FilePickerException;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;

it('creates driver not found exception', function (): void {
    $exception = DriverNotFoundException::forDriver('invalid');

    expect($exception)->toBeInstanceOf(FilePickerException::class);
    expect($exception->getMessage())->toContain('invalid');
});

it('creates plank not installed exception', function (): void {
    $exception = DriverNotFoundException::plankNotInstalled();

    expect($exception)->toBeInstanceOf(FilePickerException::class);
    expect($exception->getMessage())->toContain('plank/laravel-mediable');
});

it('creates media not found exception', function (): void {
    $exception = MediaNotFoundException::withId(42);

    expect($exception)->toBeInstanceOf(FilePickerException::class);
    expect($exception->getMessage())->toContain('42');
});

it('creates upload failed exception for storage', function (): void {
    $exception = UploadFailedException::storageFailed('test.jpg');

    expect($exception)->toBeInstanceOf(FilePickerException::class);
    expect($exception->getMessage())->toContain('test.jpg');
});

it('creates upload failed exception from previous', function (): void {
    $previous = new RuntimeException('disk full');
    $exception = UploadFailedException::fromPrevious('test.jpg', $previous);

    expect($exception)->toBeInstanceOf(FilePickerException::class);
    expect($exception->getMessage())->toContain('test.jpg');
    expect($exception->getPrevious())->toBe($previous);
});
