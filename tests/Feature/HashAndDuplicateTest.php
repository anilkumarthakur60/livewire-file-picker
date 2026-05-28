<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

it('finds media by stored hash', function (): void {
    $hash = hash('sha256', 'sample content');

    $media = FilePickerMedia::query()->create([
        'filename' => 'hashed',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'txt',
        'mime_type' => 'text/plain',
        'aggregate_type' => 'document',
        'size' => 14,
        'hash' => $hash,
    ]);

    $driver = app(MediaDriverInterface::class);
    $found = $driver->findByHash($hash);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($media->id);
});

it('returns null for unknown hash', function (): void {
    $driver = app(MediaDriverInterface::class);

    expect($driver->findByHash('00000000'))->toBeNull();
    expect($driver->findByHash(''))->toBeNull();
});

it('persists hash column on the model', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'with-hash',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'txt',
        'mime_type' => 'text/plain',
        'aggregate_type' => 'document',
        'size' => 10,
        'hash' => str_repeat('a', 64),
    ]);

    expect($media->fresh()->hash)->toBe(str_repeat('a', 64));
});
