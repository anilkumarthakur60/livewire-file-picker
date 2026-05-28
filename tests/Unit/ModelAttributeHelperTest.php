<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Models\FilePickerMedia;
use Anil\LivewireFilePicker\Support\ModelAttributeHelper;

it('extracts string attribute from model', function (): void {
    $model = new FilePickerMedia;
    $model->setAttribute('filename', 'test-file');

    expect(ModelAttributeHelper::string($model, 'filename'))->toBe('test-file');
});

it('returns default for missing string attribute', function (): void {
    $model = new FilePickerMedia;

    expect(ModelAttributeHelper::string($model, 'nonexistent', 'default'))->toBe('default');
});

it('extracts int attribute from model', function (): void {
    $model = new FilePickerMedia;
    $model->setAttribute('size', 1024);

    expect(ModelAttributeHelper::int($model, 'size'))->toBe(1024);
});

it('returns default for missing int attribute', function (): void {
    $model = new FilePickerMedia;

    expect(ModelAttributeHelper::int($model, 'nonexistent', 42))->toBe(42);
});

it('extracts nullable int attribute', function (): void {
    $model = new FilePickerMedia;
    $model->setAttribute('width', 800);

    expect(ModelAttributeHelper::nullableInt($model, 'width'))->toBe(800);
});

it('returns null for missing nullable int', function (): void {
    $model = new FilePickerMedia;

    expect(ModelAttributeHelper::nullableInt($model, 'width'))->toBeNull();
});

it('extracts nullable string attribute', function (): void {
    $model = new FilePickerMedia;
    $model->setAttribute('hash', 'test hash');

    expect(ModelAttributeHelper::nullableString($model, 'hash'))->toBe('test hash');
});

it('returns null for missing nullable string', function (): void {
    $model = new FilePickerMedia;

    expect(ModelAttributeHelper::nullableString($model, 'hash'))->toBeNull();
});

it('handles numeric string for int', function (): void {
    $model = new FilePickerMedia;
    $model->setAttribute('size', '2048');

    expect(ModelAttributeHelper::int($model, 'size'))->toBe(2048);
});
