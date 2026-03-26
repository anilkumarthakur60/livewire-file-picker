<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Enums\FileType;

it('resolves image type from extension', function (): void {
    expect(FileType::fromExtension('jpg'))->toBe(FileType::IMAGE);
    expect(FileType::fromExtension('PNG'))->toBe(FileType::IMAGE);
    expect(FileType::fromExtension('webp'))->toBe(FileType::IMAGE);
});

it('resolves video type from extension', function (): void {
    expect(FileType::fromExtension('mp4'))->toBe(FileType::VIDEO);
    expect(FileType::fromExtension('mov'))->toBe(FileType::VIDEO);
});

it('resolves audio type from extension', function (): void {
    expect(FileType::fromExtension('mp3'))->toBe(FileType::AUDIO);
    expect(FileType::fromExtension('wav'))->toBe(FileType::AUDIO);
});

it('resolves document type from extension', function (): void {
    expect(FileType::fromExtension('pdf'))->toBe(FileType::DOCUMENT);
    expect(FileType::fromExtension('docx'))->toBe(FileType::DOCUMENT);
});

it('resolves spreadsheet type from extension', function (): void {
    expect(FileType::fromExtension('xlsx'))->toBe(FileType::SPREADSHEET);
    expect(FileType::fromExtension('csv'))->toBe(FileType::SPREADSHEET);
});

it('resolves archive type from extension', function (): void {
    expect(FileType::fromExtension('zip'))->toBe(FileType::ARCHIVE);
    expect(FileType::fromExtension('tar'))->toBe(FileType::ARCHIVE);
});

it('resolves code type from extension', function (): void {
    expect(FileType::fromExtension('php'))->toBe(FileType::CODE);
    expect(FileType::fromExtension('js'))->toBe(FileType::CODE);
});

it('returns ALL for unknown extensions', function (): void {
    expect(FileType::fromExtension('xyz'))->toBe(FileType::ALL);
    expect(FileType::fromExtension(''))->toBe(FileType::ALL);
});

it('resolves type from mime type', function (): void {
    expect(FileType::fromMimeType('image/jpeg'))->toBe(FileType::IMAGE);
    expect(FileType::fromMimeType('video/mp4'))->toBe(FileType::VIDEO);
    expect(FileType::fromMimeType('audio/mpeg'))->toBe(FileType::AUDIO);
    expect(FileType::fromMimeType('application/pdf'))->toBe(FileType::DOCUMENT);
});

it('has label for every case', function (): void {
    foreach (FileType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

it('has icon for every case', function (): void {
    foreach (FileType::cases() as $type) {
        expect($type->icon())->toBeString()->not->toBeEmpty();
    }
});

it('has color for every case', function (): void {
    foreach (FileType::cases() as $type) {
        expect($type->color())->toBeString()->toStartWith('#');
    }
});

it('returns extensions as array for non-ALL types', function (): void {
    foreach (FileType::cases() as $type) {
        if ($type === FileType::ALL) {
            expect($type->extensions())->toBe([]);

            continue;
        }
        expect($type->extensions())->toBeArray()->not->toBeEmpty();
    }
});

it('returns mime types as array for non-ALL types', function (): void {
    foreach (FileType::cases() as $type) {
        if ($type === FileType::ALL) {
            expect($type->mimeTypes())->toBe([]);

            continue;
        }
        expect($type->mimeTypes())->toBeArray()->not->toBeEmpty();
    }
});
