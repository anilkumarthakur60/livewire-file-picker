<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

function makeMedia(array $overrides = []): FilePickerMedia
{
    return FilePickerMedia::query()->create(array_merge([
        'filename' => 'fav-test',
        'disk' => 'public',
        'directory' => 'media',
        'path' => 'media/fav-test.jpg',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1024,
    ], $overrides));
}

it('toggles favorite status', function (): void {
    $media = makeMedia();
    $driver = app(MediaDriverInterface::class);

    expect((bool) $media->is_favorite)->toBeFalse();

    $newState = $driver->toggleFavorite($media->id);
    expect($newState)->toBeTrue();

    $media->refresh();
    expect($media->is_favorite)->toBeTrue();

    $newState = $driver->toggleFavorite($media->id);
    expect($newState)->toBeFalse();
});

it('sets favorite to specific value', function (): void {
    $media = makeMedia();
    $driver = app(MediaDriverInterface::class);

    $driver->setFavorite($media->id, true);
    expect($media->fresh()->is_favorite)->toBeTrue();

    $driver->setFavorite($media->id, false);
    expect($media->fresh()->is_favorite)->toBeFalse();
});

it('filters favorites via scope', function (): void {
    $a = makeMedia(['filename' => 'a']);
    $b = makeMedia(['filename' => 'b']);

    $driver = app(MediaDriverInterface::class);
    $driver->setFavorite($a->id, true);

    $favorites = FilePickerMedia::query()->favorites()->get();

    expect($favorites)->toHaveCount(1);
    expect($favorites->first()->id)->toBe($a->id);
});
