<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Drivers\PlankMediaDriver;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

it('resolves driver from container', function (): void {
    $driver = app(MediaDriverInterface::class);

    expect($driver)->toBeInstanceOf(PlankMediaDriver::class);
    expect($driver->driverName())->toBe('plank');
});

it('returns empty collection for empty ids', function (): void {
    $driver = app(MediaDriverInterface::class);

    $result = $driver->findByIds([]);

    expect($result)->toBeEmpty();
});

it('checks if media exists', function (): void {
    $driver = app(MediaDriverInterface::class);

    expect($driver->exists(999))->toBeFalse();
});

it('finds media by id returns null for missing', function (): void {
    $driver = app(MediaDriverInterface::class);

    expect($driver->findById(999))->toBeNull();
});

it('throws MediaNotFoundException for missing id', function (): void {
    $driver = app(MediaDriverInterface::class);

    $driver->findByIdOrFail(999);
})->throws(MediaNotFoundException::class);

it('creates and retrieves media', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'test-image',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);

    expect($driver->exists($media->id))->toBeTrue();

    $found = $driver->findById($media->id);
    expect($found)->not->toBeNull();
    expect($found->getAttribute('filename'))->toBe('test-image');
});

it('updates alt text', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'alt-test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->updateAlt($media->id, 'Updated alt text');

    $media->refresh();
    expect($media->alt)->toBe('Updated alt text');
});

it('renames media', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'old-name',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->rename($media->id, 'new-name');

    $media->refresh();
    expect($media->filename)->toBe('new-name');
});

it('transforms media to array', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'transform-test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 2048,
        'width' => 800,
        'height' => 600,
    ]);

    $driver = app(MediaDriverInterface::class);
    $result = $driver->transform($media);

    expect($result)->toHaveKeys(['id', 'filename', 'size', 'mime_type', 'extension']);
    expect($result['filename'])->toBe('transform-test');
    expect($result['size'])->toBe(2048);
    expect($result['dimensions'])->toBe('800 x 600');
});

it('finds by ids preserving order', function (): void {
    $media1 = FilePickerMedia::query()->create([
        'filename' => 'first',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $media2 = FilePickerMedia::query()->create([
        'filename' => 'second',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 2048,
    ]);

    $driver = app(MediaDriverInterface::class);

    $results = $driver->findByIds([$media2->id, $media1->id]);

    expect($results)->toHaveCount(2);
    expect($results->first()->getAttribute('filename'))->toBe('second');
    expect($results->last()->getAttribute('filename'))->toBe('first');
});
