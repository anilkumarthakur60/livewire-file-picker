<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Support\MediaTransformer;

it('formats sizes correctly', function (): void {
    $transformer = new MediaTransformer;

    expect($transformer->formatSize(0))->toBe('0 bytes');
    expect($transformer->formatSize(512))->toBe('512 bytes');
    expect($transformer->formatSize(1024))->toBe('1.00 KB');
    expect($transformer->formatSize(1_048_576))->toBe('1.00 MB');
    expect($transformer->formatSize(1_073_741_824))->toBe('1.00 GB');
});
