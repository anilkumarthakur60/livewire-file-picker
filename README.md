# Livewire File Picker

A WordPress-like file picker component for Laravel Livewire. Supports images, videos, audio, documents, and all file types with a beautiful modal interface.

## Features

- 📁 **All File Types** - Images, videos, audio, documents, spreadsheets, presentations, archives, and code files
- 🎨 **Beautiful UI** - Modern, clean interface inspired by WordPress media library
- 🔍 **Search & Filter** - Quick search and filter by file type, folder, tag, or favorite
- 📤 **Drag & Drop Upload** - Easy file uploads with drag and drop support
- ✅ **Single/Multiple Selection** - Choose one or many files
- 🗑️ **Trash & Restore** - Soft-delete with a recoverable trash bin
- 🔁 **Replace File** - Update an existing media record's file in place
- 🪪 **Hash & Duplicate Detection** - SHA-256 dedup with `reuse`, `reject`, or `allow` strategies
- ⭐ **Favorites** - Star important items
- 🏷️ **Tags** - Tag media for richer organization and filtering
- 📂 **Folders** - Group media into folders with bulk move
- 👤 **Ownership Tracking** - Auto-record `user_id` and optionally scope library per user
- 📊 **Storage Quotas** - Global or per-user storage caps
- 📈 **Statistics API** - Aggregate counts/sizes/by-type via `FilePicker::getStats()`
- 📥 **Force Download + ZIP Bulk Download** - Per-file or multi-file zip downloads
- 🛠️ **Console Commands** - `prune-trash`, `prune-orphans`, `stats`
- 📱 **Responsive** - Works great on all devices
- ⚙️ **Highly Configurable** - Extensive configuration options + feature toggles
- 🎯 **Form Integration** - Works with Livewire and traditional forms
- ♿ **Accessible** - Proper keyboard navigation and screen reader support

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- Livewire 3.x
- [plank/laravel-mediable](https://github.com/plank/laravel-mediable) ^6.0

## Installation

### 1. Install via Composer

```bash
composer require anil/livewire-file-picker
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=file-picker-config
```

### 3. Publish Views (Optional)

```bash
php artisan vendor:publish --tag=file-picker-views
```

### 4. Set Up Laravel Mediable

Make sure you have [plank/laravel-mediable](https://github.com/plank/laravel-mediable) installed and configured:

```bash
php artisan vendor:publish --provider="Plank\Mediable\MediableServiceProvider"
php artisan migrate
```

## Usage

### Basic Usage

```blade
{{-- Single file selection --}}
@livewire('file-picker')

{{-- Multiple file selection --}}
@livewire('file-picker', ['multiple' => true])

{{-- With options --}}
@livewire('file-picker', [
    'multiple' => true,
    'maxFiles' => 5,
    'allowedTypes' => ['image', 'video'],
    'inputName' => 'gallery_images',
])
```

### In a Livewire Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class MyForm extends Component
{
    public array $selectedImages = [];

    #[On('filesSelected')]
    public function handleFilesSelected(array $files): void
    {
        $this->selectedImages = $files;
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

```blade
{{-- resources/views/livewire/my-form.blade.php --}}
<div>
    <h2>Select Images</h2>

    @livewire('file-picker', [
        'multiple' => true,
        'maxFiles' => 10,
        'selected' => $selectedImages,
        'allowedTypes' => ['image'],
    ])

    <p>Selected: {{ count($selectedImages) }} images</p>
</div>
```

### In a Traditional Blade Form

```blade
<form action="/upload" method="POST">
    @csrf

    <div>
        <label>Featured Image</label>
        @livewire('file-picker', [
            'multiple' => false,
            'inputName' => 'featured_image',
            'formId' => 'my-form',
        ])
    </div>

    <div>
        <label>Gallery Images</label>
        @livewire('file-picker', [
            'multiple' => true,
            'maxFiles' => 10,
            'inputName' => 'gallery_images',
        ])
    </div>

    <button type="submit">Save</button>
</form>
```

## Component Properties

| Property           | Type       | Default   | Description                        |
| ------------------ | ---------- | --------- | ---------------------------------- |
| `multiple`         | bool       | `false`   | Allow multiple file selection      |
| `maxFiles`         | int        | `10`      | Maximum files that can be selected |
| `selected`         | array\|int | `[]`      | Pre-selected media IDs             |
| `allowedTypes`     | array      | `[]`      | Restrict to specific file types    |
| `inputName`        | string     | `'files'` | Name attribute for hidden inputs   |
| `inputId`          | string     | auto      | ID attribute for hidden inputs     |
| `formId`           | string     | `''`      | Form ID for auto-submit feature    |
| `autoSubmit`       | bool       | `false`   | Auto-submit form on selection      |
| `callbackFunction` | string     | `''`      | JavaScript callback function name  |
| `buttonLabel`      | string     | auto      | Custom button label text           |
| `showPreview`      | bool       | `true`    | Show preview below trigger button  |
| `perPage`          | int        | `24`      | Items per page in the modal        |

## Allowed File Types

You can restrict the picker to specific file types:

```blade
@livewire('file-picker', [
    'allowedTypes' => ['image', 'video', 'audio', 'document'],
])
```

Available types:

- `all` - All file types
- `image` - jpg, jpeg, png, gif, webp, svg, bmp, ico, tiff, avif
- `video` - mp4, webm, ogg, mov, avi, mkv, wmv, flv, m4v
- `audio` - mp3, wav, aac, ogg, flac, m4a, wma, aiff
- `document` - pdf, doc, docx, txt, rtf, odt, md, epub
- `spreadsheet` - xls, xlsx, csv, ods, numbers
- `presentation` - ppt, pptx, odp, key
- `archive` - zip, rar, 7z, tar, gz, bz2, xz
- `code` - js, ts, php, html, css, json, etc.

## Events

### Livewire Events

```php
// Listen for file selection
#[On('filesSelected')]
public function handleFilesSelected(array $files): void
{
    $this->selectedImages = $files;
}

// Listen for media deletion
#[On('media-deleted')]
public function handleMediaDeleted(int $mediaId): void
{
    // Handle deletion
}

// Listen for media refresh
#[On('media-refreshed')]
public function handleMediaRefreshed(): void
{
    // Handle refresh
}
```

### JavaScript Events

```javascript
// Listen for file selection
window.addEventListener("file-picker:selected", (event) => {
    console.log("Selected files:", event.detail.selected);
    console.log("Input name:", event.detail.inputName);
});
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=file-picker-config
```

### config/file-picker.php

```php
return [
    // Storage settings
    'disk' => env('FILE_PICKER_DISK', 'public'),
    'directory' => env('FILE_PICKER_DIRECTORY', 'media'),
    'visibility' => env('FILE_PICKER_VISIBILITY', 'public'),

    // Upload limits (in KB)
    'max_file_size' => env('FILE_PICKER_MAX_SIZE', 102400), // 100MB

    // Default settings
    'defaults' => [
        'multiple' => false,
        'max_files' => 10,
        'per_page' => 24,
        'show_preview' => true,
    ],

    // Allowed file types (use 'all' for no restrictions)
    'allowed_types' => ['all'],

    // UI customization
    'ui' => [
        'modal_size' => '4xl',
        'thumbnail_height' => 150,
        'show_type_badges' => true,
        'show_file_size' => true,
        'show_date' => true,
        'primary_color' => '#0073aa',
        'danger_color' => '#ef4444',
        'success_color' => '#10b981',
    ],

    // Feature toggles
    'features' => [
        'upload' => true,
        'delete' => true,
        'edit_alt' => true,
        'search' => true,
        'filter' => true,
        'drag_drop' => true,
        'refresh' => true,
    ],

    // Text strings (can be localized)
    'texts' => [
        'modal_title' => 'Media Library',
        'upload_tab' => 'Upload Files',
        // ... more texts
    ],
];
```

## Customization

### Custom Styles

You can override the CSS variables in your app's stylesheet:

```css
:root {
    --fp-primary: #3b82f6;
    --fp-danger: #ef4444;
    --fp-success: #10b981;
    /* ... more variables */
}
```

### Custom Views

Publish and modify the views:

```bash
php artisan vendor:publish --tag=file-picker-views
```

Views will be published to `resources/views/vendor/file-picker/`.

## API Reference

### FilePicker Component Methods

| Method                          | Description                                         |
| ------------------------------- | --------------------------------------------------- |
| `openModal()` / `closeModal()`  | Open / close the modal                              |
| `setViewMode('library'\|'trash')` | Switch between active library and trash             |
| `toggleSelection($id)`          | Toggle selection of a media item                    |
| `clearSelection()`              | Clear all selected items                            |
| `insertSelected()`              | Confirm selection and close modal                   |
| `uploadFiles()`                 | Upload pending files                                |
| `deleteMedia($id)`              | Soft-delete a media item (move to trash)            |
| `restoreMedia($id)`             | Restore from trash                                  |
| `forceDeleteMedia($id)`         | Permanently delete (and remove file from disk)      |
| `bulkDelete($ids)`              | Soft-delete many at once                            |
| `toggleFavorite($id)`           | Toggle favorite                                     |
| `addTag()` / `removeTag($id, $tag)` | Manage tags on a media item                     |
| `startMoving($id)` + `saveMove()` | Move a media item to a folder                     |
| `bulkMoveToFolder($ids, $folder)` | Move many at once                                 |
| `startReplacing($id)`           | Replace the underlying file (next upload swaps it)  |
| `refreshMedia()`                | Reload media items                                  |
| `clearFilters()`                | Reset search/type/folder/tag/favorite filters       |

### FilePicker Facade

Outside the component, drive the library directly:

```php
use Anil\LivewireFilePicker\Facades\FilePicker;

FilePicker::upload($temporaryFile, ['folder' => 'reports', 'tags' => ['q1', 'finance']]);
FilePicker::replaceFile($id, $newFile);
FilePicker::toggleFavorite($id);
FilePicker::addTag($id, 'archive-2024');
FilePicker::moveToFolder($id, 'archive/2024');
FilePicker::restore($id);
FilePicker::forceDelete($id);
FilePicker::getStats();          // counts, sizes, by_type, favorites_count, trashed_count
FilePicker::findByHash($sha256); // dedup lookups
```

### Console Commands

```bash
php artisan file-picker:prune-trash --days=30 --dry-run
php artisan file-picker:prune-orphans --dry-run
php artisan file-picker:stats
```

### Download Routes

| Route                                  | Purpose                              |
| -------------------------------------- | ------------------------------------ |
| `GET /file-picker/download/{id}`       | Force-download a single file         |
| `GET /file-picker/download-zip?ids[]=` | Stream a zip of selected media       |

### Computed Properties

| Property             | Type   | Description                    |
| -------------------- | ------ | ------------------------------ |
| `selectedMediaItems` | array  | Full details of selected media |
| `hasSelection`       | bool   | Whether any items are selected |
| `selectionLabel`     | string | Human-readable selection count |
| `selectedCount`      | int    | Number of selected items       |

## Migration from v1

If upgrading from v1.x:

1. Update your composer.json to require `^2.0`
2. The component name remains `file-picker`
3. Events have been renamed:
    - `imagesSelected` → `filesSelected`
    - `file-picker-selected` remains the same
4. Configuration file structure has changed - republish config

## Static Analysis

This package uses [PHPStan](https://phpstan.org/) with [Larastan](https://github.com/larastan/larastan) for static analysis at **level 8** (the strictest level).

### Running PHPStan

```bash
# Run static analysis
composer analyse

# Or directly with PHPStan
./vendor/bin/phpstan analyse
```

The configuration is in `phpstan.neon` and includes:

- Level 8 (strictest type checking)
- Laravel-specific rules via Larastan
- Custom ignore patterns for Livewire trait patterns

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Er. Anil Kumar Thakur](https://github.com/anilkumarthakur60)
- [All Contributors](../../contributors)
