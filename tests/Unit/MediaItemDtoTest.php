<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\DTOs\MediaItem;
use Anil\LivewireFilePicker\Enums\FileType;
use Carbon\Carbon;

it('formats file sizes correctly', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.jpg',
        thumbnailUrl: '/test.jpg',
        size: 1024,
        mimeType: 'image/jpeg',
        extension: 'jpg',
        aggregateType: 'image',
        alt: null,
        fileType: FileType::IMAGE,
        icon: '',
        createdAt: Carbon::now(),
    );

    expect($item->formattedSize())->toBe('1.00 KB');
});

it('formats megabyte sizes', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.pdf',
        thumbnailUrl: null,
        size: 1_048_576,
        mimeType: 'application/pdf',
        extension: 'pdf',
        aggregateType: 'document',
        alt: null,
        fileType: FileType::DOCUMENT,
        icon: '',
        createdAt: Carbon::now(),
    );

    expect($item->formattedSize())->toBe('1.00 MB');
});

it('formats gigabyte sizes', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.mp4',
        thumbnailUrl: null,
        size: 1_073_741_824,
        mimeType: 'video/mp4',
        extension: 'mp4',
        aggregateType: 'video',
        alt: null,
        fileType: FileType::VIDEO,
        icon: '',
        createdAt: Carbon::now(),
    );

    expect($item->formattedSize())->toBe('1.00 GB');
});

it('returns dimensions when width and height are set', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.jpg',
        thumbnailUrl: '/test.jpg',
        size: 1024,
        mimeType: 'image/jpeg',
        extension: 'jpg',
        aggregateType: 'image',
        alt: null,
        fileType: FileType::IMAGE,
        icon: '',
        createdAt: Carbon::now(),
        width: 1920,
        height: 1080,
    );

    expect($item->dimensions())->toBe('1920 x 1080');
});

it('returns null dimensions when not set', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.pdf',
        thumbnailUrl: null,
        size: 1024,
        mimeType: 'application/pdf',
        extension: 'pdf',
        aggregateType: 'document',
        alt: null,
        fileType: FileType::DOCUMENT,
        icon: '',
        createdAt: Carbon::now(),
    );

    expect($item->dimensions())->toBeNull();
});

it('formats duration correctly', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.mp3',
        thumbnailUrl: null,
        size: 1024,
        mimeType: 'audio/mpeg',
        extension: 'mp3',
        aggregateType: 'audio',
        alt: null,
        fileType: FileType::AUDIO,
        icon: '',
        createdAt: Carbon::now(),
        duration: 185,
    );

    expect($item->formattedDuration())->toBe('3:05');
});

it('returns null duration when not set', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.mp3',
        thumbnailUrl: null,
        size: 1024,
        mimeType: 'audio/mpeg',
        extension: 'mp3',
        aggregateType: 'audio',
        alt: null,
        fileType: FileType::AUDIO,
        icon: '',
        createdAt: Carbon::now(),
    );

    expect($item->formattedDuration())->toBeNull();
});

it('correctly identifies file types', function (): void {
    $image = new MediaItem(1, 'test', '', null, 0, 'image/jpeg', 'jpg', 'image', null, FileType::IMAGE, '', Carbon::now());
    $video = new MediaItem(1, 'test', '', null, 0, 'video/mp4', 'mp4', 'video', null, FileType::VIDEO, '', Carbon::now());
    $audio = new MediaItem(1, 'test', '', null, 0, 'audio/mpeg', 'mp3', 'audio', null, FileType::AUDIO, '', Carbon::now());
    $doc = new MediaItem(1, 'test', '', null, 0, 'application/pdf', 'pdf', 'document', null, FileType::DOCUMENT, '', Carbon::now());

    expect($image->isImage())->toBeTrue();
    expect($image->isVideo())->toBeFalse();
    expect($video->isVideo())->toBeTrue();
    expect($audio->isAudio())->toBeTrue();
    expect($doc->isDocument())->toBeTrue();
});

it('converts to array with all keys', function (): void {
    $item = new MediaItem(
        id: 1,
        filename: 'test',
        url: '/test.jpg',
        thumbnailUrl: '/test.jpg',
        size: 1024,
        mimeType: 'image/jpeg',
        extension: 'jpg',
        aggregateType: 'image',
        alt: 'A test image',
        fileType: FileType::IMAGE,
        icon: 'M0 0',
        createdAt: Carbon::parse('2024-06-15'),
        width: 800,
        height: 600,
    );

    $array = $item->toArray();

    expect($array)->toHaveKeys([
        'id', 'filename', 'url', 'thumbnail_url', 'size', 'size_formatted',
        'mime_type', 'extension', 'aggregate_type', 'alt', 'file_type',
        'file_type_label', 'file_type_color', 'icon', 'created_at',
        'created_at_formatted', 'created_at_diff', 'width', 'height',
        'dimensions', 'duration', 'duration_formatted',
    ]);

    expect($array['id'])->toBe(1);
    expect($array['filename'])->toBe('test');
    expect($array['alt'])->toBe('A test image');
    expect($array['dimensions'])->toBe('800 x 600');
});
