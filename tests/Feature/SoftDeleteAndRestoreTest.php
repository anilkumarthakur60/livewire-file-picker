<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

it('soft-deletes media via delete', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'soft-delete-test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->delete($media->id);

    expect($driver->findById($media->id))->toBeNull();
    expect($driver->queryOnlyTrashed()->where('id', $media->id)->exists())->toBeTrue();
});

it('restores trashed media', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'restore-test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->delete($media->id);
    $driver->restore($media->id);

    expect($driver->findById($media->id))->not->toBeNull();
});

it('restoring missing media throws', function (): void {
    $driver = app(MediaDriverInterface::class);
    $driver->restore(9999);
})->throws(MediaNotFoundException::class);

it('force-deletes a trashed item', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'force-test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->delete($media->id);
    $driver->forceDelete($media->id);

    expect($driver->queryWithTrashed()->where('id', $media->id)->exists())->toBeFalse();
});
