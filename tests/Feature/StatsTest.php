<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

it('returns expected stats keys', function (): void {
    FilePickerMedia::query()->create([
        'filename' => 's1',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1000,
    ]);

    FilePickerMedia::query()->create([
        'filename' => 's2',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'pdf',
        'mime_type' => 'application/pdf',
        'aggregate_type' => 'pdf',
        'size' => 2500,
    ]);

    $driver = app(MediaDriverInterface::class);
    $stats = $driver->getStats();

    expect($stats)->toHaveKeys([
        'total_count', 'total_size', 'favorites_count', 'trashed_count',
        'folders_count', 'tags_count', 'by_type',
    ]);
    expect($stats['total_count'])->toBe(2);
    expect($stats['total_size'])->toBe(3500);
});

it('counts trashed in stats', function (): void {
    $a = FilePickerMedia::query()->create([
        'filename' => 't1',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1000,
    ]);

    $driver = app(MediaDriverInterface::class);
    $driver->delete($a->id);

    $stats = $driver->getStats();

    expect($stats['trashed_count'])->toBe(1);
    expect($stats['total_count'])->toBe(0);
});
