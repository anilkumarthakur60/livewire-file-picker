<?php
declare(strict_types=1);

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Models\FilePickerMedia;

function tagFolderMedia(array $overrides = []): FilePickerMedia
{
    return FilePickerMedia::query()->create(array_merge([
        'filename'  => 'tag-test',
        'disk'      => 'public',
        'directory' => 'media',
        'path'      => 'media/tag-test.jpg',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'size'      => 1024,
    ], $overrides));
}

it('adds and removes tags', function (): void {
    $media = tagFolderMedia();
    $driver = app(MediaDriverInterface::class);
    $driver->addTag($media->id, 'nature');
    $driver->addTag($media->id, 'landscape');
    expect($media->fresh()->tags)->toBe(['nature', 'landscape']);
    $driver->removeTag($media->id, 'nature');
    expect($media->fresh()->tags)->toBe(['landscape']);
});
it('does not add duplicate tag', function (): void {
    $media = tagFolderMedia();
    $driver = app(MediaDriverInterface::class);
    $driver->addTag($media->id, 'urban');
    $driver->addTag($media->id, 'urban');
    expect($media->fresh()->tags)->toBe(['urban']);
});
it('replaces all tags via setTags', function (): void {
    $media = tagFolderMedia();
    $driver = app(MediaDriverInterface::class);
    $driver->setTags($media->id, ['a', 'b', 'c']);
    expect($media->fresh()->tags)->toBe(['a', 'b', 'c']);
    $driver->setTags($media->id, ['z']);
    expect($media->fresh()->tags)->toBe(['z']);
});
it('lists all distinct tags', function (): void {
    $a = tagFolderMedia(['filename' => 'one']);
    $b = tagFolderMedia(['filename' => 'two']);
    $driver = app(MediaDriverInterface::class);
    $driver->setTags($a->id, ['nature', 'beach']);
    $driver->setTags($b->id, ['urban', 'nature']);
    $tags = $driver->getAllTags();
    sort($tags);
    expect($tags)->toBe(['beach', 'nature', 'urban']);
});
it('moves media into folder', function (): void {
    $media = tagFolderMedia();
    $driver = app(MediaDriverInterface::class);
    $driver->moveToFolder($media->id, 'photos/2024');
    $fresh = $media->fresh();
    expect($fresh->folder)->toBe('photos/2024');
    expect($fresh->directory)->toBe('media/photos/2024');
    expect($fresh->path)->toBe('media/photos/2024/tag-test.jpg');
    $driver->moveToFolder($media->id, null);
    $fresh = $media->fresh();
    expect($fresh->folder)->toBeNull();
    expect($fresh->directory)->toBe('media');
    expect($fresh->path)->toBe('media/tag-test.jpg');
});
it('lists distinct folders', function (): void {
    $a = tagFolderMedia(['filename' => 'one']);
    $b = tagFolderMedia(['filename' => 'two']);
    $c = tagFolderMedia(['filename' => 'three']);
    $driver = app(MediaDriverInterface::class);
    $driver->moveToFolder($a->id, 'photos');
    $driver->moveToFolder($b->id, 'docs');
    // c stays at root
    $folders = $driver->getFolders();
    sort($folders);
    expect($folders)->toBe(['docs', 'photos']);
});
it('bulk-moves multiple items', function (): void {
    $a = tagFolderMedia(['filename' => 'one']);
    $b = tagFolderMedia(['filename' => 'two']);
    $driver = app(MediaDriverInterface::class);
    $moved = $driver->bulkMoveToFolder([$a->id, $b->id], 'archive');
    expect($moved)->toBe(2);
    expect($a->fresh()->folder)->toBe('archive');
    expect($b->fresh()->folder)->toBe('archive');
});
