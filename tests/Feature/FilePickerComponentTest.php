<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Livewire\FilePicker;
use Anil\LivewireFilePicker\Models\FilePickerMedia;
use Livewire\Livewire;

it('renders the file picker component', function (): void {
    Livewire::test(FilePicker::class)
        ->assertStatus(200);
});

it('mounts with default values', function (): void {
    Livewire::test(FilePicker::class)
        ->assertSet('multiple', false)
        ->assertSet('maxFiles', 10)
        ->assertSet('selected', [])
        ->assertSet('showModal', false)
        ->assertSet('currentTab', 'library');
});

it('mounts with custom values', function (): void {
    Livewire::test(FilePicker::class, [
        'multiple' => true,
        'maxFiles' => 5,
        'inputName' => 'gallery',
        'allowedTypes' => ['image', 'video'],
    ])
        ->assertSet('multiple', true)
        ->assertSet('maxFiles', 5)
        ->assertSet('inputName', 'gallery')
        ->assertSet('allowedTypes', ['image', 'video']);
});

it('opens and closes modal', function (): void {
    Livewire::test(FilePicker::class)
        ->assertSet('showModal', false)
        ->call('openModal')
        ->assertSet('showModal', true)
        ->call('closeModal')
        ->assertSet('showModal', false);
});

it('switches tabs', function (): void {
    Livewire::test(FilePicker::class)
        ->call('openModal')
        ->assertSet('currentTab', 'library')
        ->call('setTab', 'upload')
        ->assertSet('currentTab', 'upload')
        ->call('setTab', 'library')
        ->assertSet('currentTab', 'library');
});

it('toggles single selection', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class)
        ->call('toggleSelection', $media->id)
        ->assertSet('selected', [$media->id])
        ->call('toggleSelection', $media->id)
        ->assertSet('selected', []);
});

it('toggles multiple selection', function (): void {
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

    Livewire::test(FilePicker::class, ['multiple' => true])
        ->call('toggleSelection', $media1->id)
        ->assertSet('selected', [$media1->id])
        ->call('toggleSelection', $media2->id)
        ->assertSet('selected', [$media1->id, $media2->id]);
});

it('respects max files limit', function (): void {
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

    Livewire::test(FilePicker::class, ['multiple' => true, 'maxFiles' => 1])
        ->call('toggleSelection', $media1->id)
        ->assertSet('selected', [$media1->id])
        ->call('toggleSelection', $media2->id)
        ->assertSet('selected', [$media1->id]); // Should not add second
});

it('clears selection', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class)
        ->call('toggleSelection', $media->id)
        ->assertSet('selected', [$media->id])
        ->call('clearSelection')
        ->assertSet('selected', []);
});

it('loads media items when modal opens', function (): void {
    FilePickerMedia::query()->create([
        'filename' => 'test',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $component = Livewire::test(FilePicker::class)
        ->call('openModal');

    expect($component->get('mediaItems'))->toHaveCount(1);
});

it('deletes media and removes from selection', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'to-delete',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class)
        ->call('toggleSelection', $media->id)
        ->assertSet('selected', [$media->id])
        ->call('deleteMedia', $media->id)
        ->assertSet('selected', []);

    expect(FilePickerMedia::query()->find($media->id))->toBeNull();
});

it('sorts media', function (): void {
    Livewire::test(FilePicker::class)
        ->assertSet('sortField', 'created_at')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'filename')
        ->assertSet('sortField', 'filename')
        ->assertSet('sortDirection', 'desc')
        ->call('sort', 'filename')
        ->assertSet('sortDirection', 'asc');
});

it('ignores invalid sort fields', function (): void {
    Livewire::test(FilePicker::class)
        ->call('sort', 'invalid_field')
        ->assertSet('sortField', 'created_at');
});

it('paginates media', function (): void {
    // Create 30 items
    for ($i = 0; $i < 30; $i++) {
        FilePickerMedia::query()->create([
            'filename' => "file-{$i}",
            'disk' => 'public',
            'directory' => 'media',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'aggregate_type' => 'image',
            'size' => 1024,
        ]);
    }

    $component = Livewire::test(FilePicker::class, ['perPage' => 10])
        ->call('openModal');

    expect($component->get('mediaItems'))->toHaveCount(10);
    expect($component->get('totalItems'))->toBe(30);
    expect($component->get('totalPages'))->toBe(3);
    expect($component->get('currentPage'))->toBe(1);

    $component->call('nextPage');
    expect($component->get('currentPage'))->toBe(2);

    $component->call('previousPage');
    expect($component->get('currentPage'))->toBe(1);
});

it('mounts with pre-selected ids', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'pre-selected',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class, [
        'selected' => [$media->id],
    ])
        ->assertSet('selected', [$media->id]);
});

it('mounts with single int selected as array', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'mount-int',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class, [
        'selected' => [$media->id],
    ])
        ->assertSet('selected', [$media->id]);
});

it('drops trashed ids from initial selected on mount', function (): void {
    $active = FilePickerMedia::query()->create([
        'filename' => 'active',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    $trashed = FilePickerMedia::query()->create([
        'filename' => 'trashed',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);
    $trashed->delete();

    Livewire::test(FilePicker::class, [
        'selected' => [$active->id, $trashed->id, 9999],
    ])
        ->assertSet('selected', [$active->id]);
});

it('refuses to add a trashed item to selection via toggleSelection', function (): void {
    $trashed = FilePickerMedia::query()->create([
        'filename' => 'trashed',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);
    $trashed->delete();

    Livewire::test(FilePicker::class, ['multiple' => true])
        ->call('openModal')
        ->call('setViewMode', 'trash')
        ->call('toggleSelection', $trashed->id)
        ->assertSet('selected', [])
        ->assertSet('activeTrashId', $trashed->id);
});

it('starts and cancels editing alt text', function (): void {
    Livewire::test(FilePicker::class)
        ->call('startEditing', 1, 'old alt')
        ->assertSet('editingMediaId', 1)
        ->assertSet('editingAlt', 'old alt')
        ->call('cancelEditing')
        ->assertSet('editingMediaId', null)
        ->assertSet('editingAlt', '');
});

it('starts and cancels renaming', function (): void {
    Livewire::test(FilePicker::class)
        ->call('startRenaming', 1, 'old-name')
        ->assertSet('renamingMediaId', 1)
        ->assertSet('renamingFilename', 'old-name')
        ->call('cancelRenaming')
        ->assertSet('renamingMediaId', null)
        ->assertSet('renamingFilename', '');
});

it('dispatches selection events on insert', function (): void {
    $media = FilePickerMedia::query()->create([
        'filename' => 'selected',
        'disk' => 'public',
        'directory' => 'media',
        'extension' => 'jpg',
        'mime_type' => 'image/jpeg',
        'aggregate_type' => 'image',
        'size' => 1024,
    ]);

    Livewire::test(FilePicker::class)
        ->call('openModal')
        ->call('toggleSelection', $media->id)
        ->call('insertSelected')
        ->assertDispatched('filesSelected')
        ->assertDispatched('file-picker-selected')
        ->assertSet('showModal', false);
});
