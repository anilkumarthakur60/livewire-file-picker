# Livewire File Picker

A WordPress-like file picker component for Laravel Livewire. Supports images, videos, audio, documents, and all file types with a beautiful modal interface.

## Features

- 📁 **All File Types** — Images, videos, audio, documents, spreadsheets, presentations, archives, and code files
- 🎨 **Beautiful UI** — Modern, clean interface inspired by WordPress media library, with a responsive Sheet-style detail panel on tablet/mobile
- 🔍 **Search & Filter** — Quick search and filter by file type, folder, tag, or favorite
- 📤 **Drag & Drop + Paste** — Drag-drop files in, or paste from clipboard
- ✅ **Single / Multiple Selection** — Configurable max-files
- 🗑️ **Trash & Restore** — Soft-delete with recoverable trash + retention-based pruning
- 🔁 **Replace File** — Update an existing media record's file in place
- 🪪 **Hash & Duplicate Detection** — SHA-256 dedup with `reuse`, `reject`, or `allow` strategies
- ⭐ **Favorites** — Star important items
- 🏷️ **Tags** — Free-form labels for richer organization
- 📂 **Folders** — Group media into folders with single & bulk move
- ✏️ **Inline Editing** — Rename and edit alt text without leaving the picker
- 👤 **Ownership Tracking** — Auto-record `user_id`, optionally scope library per user
- 📊 **Storage Quotas** — Global and per-user storage caps
- 📈 **Statistics API** — Aggregate counts / sizes / by-type via `FilePicker::getStats()`
- 📥 **Downloads** — Force-download single files or bulk download as ZIP
- 🛠️ **Console Commands** — `file-picker:prune-trash`, `file-picker:prune-orphans`, `file-picker:stats`
- ⚙️ **Highly Configurable** — Feature toggles, theme colors, custom drivers, custom authorization, custom filters
- 🎯 **Form Integration** — Works with Livewire components and traditional HTML forms
- ♿ **Accessible** — Keyboard navigation, focus management, Esc-to-close

## Requirements

- PHP 8.2+ (also tested on 8.3 / 8.4)
- Laravel 11.x, 12.x, or 13.x
- Livewire 3.x or 4.x
- [plank/laravel-mediable](https://github.com/plank/laravel-mediable) ^6.0 — installed automatically

## Installation

### 1. Install via Composer

```bash
composer require anil/file-picker
```

### 2. Run the install command

```bash
php artisan file-picker:install
```

This single command will:
- Publish `config/file-picker.php`
- Run the package migration — an additive migration that adds the package's columns (`folder`, `tags`, `is_favorite`, `hash`, etc.) to Plank's `media` table

That's it. Assets (CSS/JS) are served automatically via a built-in route — no need to publish them.

### Optional flags

```bash
# Overwrite already-published files
php artisan file-picker:install --force

# Skip running migrations (publish only)
php artisan file-picker:install --no-migrate

# Also publish blade views for UI customisation
php artisan file-picker:install --views

# Also publish language files to override text strings
php artisan file-picker:install --lang

# Publish CSS/JS to public/ (not required — served via route by default)
php artisan file-picker:install --assets
```

### 3. Add stack slots to your layout

The component pushes its CSS into `@stack('head')` and its JS into `@stack('scripts')`. Add these to your layout if not already present:

```blade
<!DOCTYPE html>
<html>
<head>
    ...
    @stack('head')
</head>
<body>
    ...
    {{ $slot }}

    @stack('scripts')
</body>
</html>
```

## Usage

### Basic usage

```blade
{{-- Single file selection --}}
<livewire:file-picker input-name="featured_image" />

{{-- Multiple file selection --}}
<livewire:file-picker input-name="gallery" :multiple="true" :max-files="5" />

{{-- Restrict to specific file types --}}
<livewire:file-picker input-name="avatar" :allowed-types="['image']" />
```

### In a Livewire component

```php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class PostForm extends Component
{
    public array $selectedMedia = [];

    #[On('file-picker-selected')]
    public function handleFilePickerSelected(array $selected, string $inputName): void
    {
        $this->selectedMedia = $selected;
    }

    public function render()
    {
        return view('livewire.post-form');
    }
}
```

```blade
{{-- resources/views/livewire/post-form.blade.php --}}
<div>
    <livewire:file-picker
        input-name="media"
        :multiple="true"
        :max-files="10"
        :selected="$selectedMedia"
        :allowed-types="['image', 'video']"
    />

    <p>Selected: {{ count($selectedMedia) }} files</p>
</div>
```

### In a traditional HTML form

```blade
<form id="my-form" action="/posts" method="POST">
    @csrf

    {{-- Single file — populates a hidden input on selection --}}
    <livewire:file-picker
        input-name="featured_image"
        form-id="my-form"
    />

    {{-- Multiple files — auto-submit form after selection --}}
    <livewire:file-picker
        input-name="gallery[]"
        :multiple="true"
        :max-files="10"
        form-id="my-form"
        :auto-submit="true"
    />

    <button type="submit">Save</button>
</form>
```

### With a JavaScript callback

```blade
<livewire:file-picker
    input-name="media"
    callback-function="onMediaSelected"
/>

<script>
function onMediaSelected(selected, inputName, inputId) {
    console.log('Selected media:', selected);
}
</script>
```

## Component Properties

| Property           | Type          | Default    | Description                                        |
|--------------------|---------------|------------|----------------------------------------------------|
| `multiple`         | `bool`        | `false`    | Allow multiple file selection                      |
| `maxFiles`         | `int`         | `10`       | Maximum number of files that can be selected       |
| `selected`         | `array`       | `[]`       | Pre-selected media IDs                             |
| `allowedTypes`     | `array`       | `[]`       | Restrict to specific file types (empty = all)      |
| `inputName`        | `string`      | `'files'`  | Name attribute for the hidden input(s)             |
| `inputId`          | `string`      | auto       | ID attribute for the hidden input                  |
| `formId`           | `string`      | `''`       | Form ID to target for auto-submit                  |
| `autoSubmit`       | `bool`        | `false`    | Auto-submit the form after selection               |
| `callbackFunction` | `string`      | `''`       | Global JS function name called after selection     |
| `buttonLabel`      | `string`      | auto       | Override the trigger button label                  |
| `showPreview`      | `bool`        | `true`     | Show selected file previews below the button       |
| `perPage`          | `int`         | `24`       | Items per page in the media library                |

## Allowed File Types

Restrict the picker to one or more types via `allowedTypes`:

```blade
<livewire:file-picker :allowed-types="['image', 'document']" />
```

| Type           | Extensions                                                            |
|----------------|-----------------------------------------------------------------------|
| `image`        | jpg, jpeg, png, gif, webp, svg, bmp, ico, tiff, avif                 |
| `video`        | mp4, webm, ogg, mov, avi, mkv, wmv, flv, m4v                        |
| `audio`        | mp3, wav, aac, ogg, flac, m4a, wma, aiff                            |
| `document`     | pdf, doc, docx, txt, rtf, odt, md, epub                             |
| `spreadsheet`  | xls, xlsx, csv, ods, numbers                                         |
| `presentation` | ppt, pptx, odp, key                                                  |
| `archive`      | zip, rar, 7z, tar, gz, bz2, xz                                       |
| `code`         | js, ts, php, html, css, json, yaml, vue, jsx, tsx, py, go, rs, etc. |

You can customize extensions per type in `config/file-picker.php` under `extensions`.

## Events

### JavaScript event

Fired on the `window` after the user confirms their selection:

```javascript
window.addEventListener('file-picker:selected', (event) => {
    const { selected, inputName, inputId } = event.detail;
    console.log('Selected media:', selected);
});
```

Each item in `selected` is an object with: `id`, `url`, `filename`, `size`, `extension`, `file_type`, `alt`, `created_at`.

### Livewire event

The component dispatches `file-picker-selected` which you can listen for in a parent Livewire component:

```php
#[On('file-picker-selected')]
public function onFilePickerSelected(array $selected, string $inputName, string $inputId): void
{
    $this->selectedIds = array_column($selected, 'id');
}
```

## Drivers

### Plank driver (default)

The package is built on top of [plank/laravel-mediable](https://github.com/plank/laravel-mediable) — installed automatically as a hard dependency. The bundled `FilePickerMedia` model extends Plank's `Media` model and the install migration adds the extra columns we need (`folder`, `tags`, `is_favorite`, `hash`, `width`, `height`, `duration`, `user_id`, `download_count`, `custom_properties`, `deleted_at`) to Plank's `media` table.

Defaults:

```env
FILE_PICKER_DRIVER=plank
FILE_PICKER_DISK=public
FILE_PICKER_DIRECTORY=media
```

> **Using a non-public disk?** Plank's `mediable.allowed_disks` config defaults to `['public']` only. To use `s3` or another disk, publish Plank's config (`php artisan vendor:publish --tag=mediable-config`) and add your disk to `allowed_disks`.

### Custom driver

Implement `Anil\LivewireFilePicker\Contracts\MediaDriverInterface` (or extend `Anil\LivewireFilePicker\Drivers\AbstractDriver`) and register the FQCN as the driver:

```php
// config/file-picker.php
'driver' => \App\Media\MyCustomDriver::class,
```

## Authorization

By default all actions are permitted. To add authorization, implement `FilePickerAuthorizationInterface`:

```php
namespace App\Auth;

use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;

class MediaAuthorization implements FilePickerAuthorizationInterface
{
    public function canViewLibrary(): bool
    {
        return auth()->check();
    }

    public function canUpload(): bool
    {
        return auth()->user()?->can('upload-media') ?? false;
    }

    public function canDelete(int $mediaId): bool
    {
        return auth()->user()?->can('delete-media') ?? false;
    }

    public function canEditAlt(int $mediaId): bool
    {
        return auth()->check();
    }
}
```

Register it in the config:

```php
// config/file-picker.php
'authorization_class' => \App\Auth\MediaAuthorization::class,
```

## Custom Filters

Add custom filter controls to the media library toolbar. Two parts are required:

**1. Define the UI controls in config:**

```php
// config/file-picker.php
'ui' => [
    'custom_filters' => [
        [
            'name'        => 'tag',
            'label'       => 'Tag',
            'type'        => 'select',        // select | text | checkbox | date_range
            'placeholder' => 'All Tags',
            'options'     => [
                ''       => 'All Tags',
                'nature' => 'Nature',
                'urban'  => 'Urban',
            ],
        ],
        [
            'name'  => 'featured',
            'label' => 'Featured Only',
            'type'  => 'checkbox',
        ],
    ],
    'custom_filter_class' => \App\Filters\MediaFilter::class,
],
```

**2. Implement the filter class:**

```php
namespace App\Filters;

use Anil\LivewireFilePicker\Contracts\CustomFilter;
use Illuminate\Database\Eloquent\Builder;

class MediaFilter implements CustomFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        if (!empty($filters['tag'])) {
            $query->where('tag', $filters['tag']);
        }

        if (!empty($filters['featured'])) {
            $query->where('featured', true);
        }

        return $query;
    }
}
```

## Configuration Reference

Publish the config to customize everything:

```bash
php artisan vendor:publish --tag=file-picker-config
```

### Driver

```php
'driver' => env('FILE_PICKER_DRIVER', 'plank'), // 'plank' | CustomDriver::class

'drivers' => [
    'plank' => [
        'model'      => FilePickerMedia::class, // extends Plank\Mediable\Media
        'disk'       => env('FILE_PICKER_DISK', 'public'),
        'directory'  => env('FILE_PICKER_DIRECTORY', 'media'),
        'visibility' => env('FILE_PICKER_VISIBILITY', 'public'),
    ],
],
```

### Upload limits

```php
'max_file_size' => env('FILE_PICKER_MAX_SIZE', 102400), // KB (default: 100 MB)
```

### Defaults

```php
'defaults' => [
    'multiple'     => false,
    'max_files'    => 40,
    'per_page'     => 24,
    'show_preview' => true,
],
```

### Sorting

```php
'sorting' => [
    'field'     => 'created_at', // created_at | filename | size | extension
    'direction' => 'desc',       // asc | desc
],
```

### Feature toggles

```php
'features' => [
    'upload'              => true,
    'delete'              => true,
    'bulk_delete'         => true,
    'edit_alt'            => true,
    'rename'              => true,
    'search'              => true,
    'filter'              => true,
    'sorting'             => true,
    'drag_drop'           => true,
    'refresh'             => true,
    'keyboard_navigation' => true,
    'paste_upload'        => true,
],
```

### UI / Theme

```php
'ui' => [
    'modal_style'           => 'fullscreen',   // 'fullscreen' | 'centered'
    'thumbnail_height'      => 150,            // px
    'show_type_badges'      => true,
    'show_file_size'        => true,
    'show_date'             => true,

    'colors' => [
        'primary'       => '#0073aa',
        'primary_hover' => '#005a87',
        'danger'        => '#ef4444',
        'success'       => '#10b981',
        'warning'       => '#f59e0b',
    ],

    'font_family'           => "'Inter', sans-serif",
    'border_radius'         => 8,              // px
    'grid_min_width'        => 160,            // px
    'grid_gap'              => 14,             // px
    'sidebar_width'         => 300,            // px
    'backdrop_blur'         => 12,             // px
    'backdrop_opacity'      => 0.6,
    'z_index'               => 9999,
    'upload_preview_size'   => 120,            // px
    'upload_area_max_height'=> 400,            // px

    'filter_types' => ['image', 'document', 'video', 'audio', 'spreadsheet', 'presentation'],

    'custom_filters'      => [],
    'custom_filter_class' => '',
],
```

### Text strings

All text in the UI is configurable and can also be translated via published lang files:

```bash
php artisan file-picker:install --lang
# or
php artisan vendor:publish --tag=file-picker-lang
```

```php
'texts' => [
    'modal_title'        => 'Media Library',
    'tab_upload'         => 'Upload Files',
    'tab_library'        => 'Media Library',
    'drop_zone'          => 'Drop files here or click to upload',
    'search_placeholder' => 'Search media...',
    'no_items'           => 'No media found',
    'insert_button'      => 'Insert Selected',
    'delete_confirm'     => 'Are you sure you want to delete this file?',
    // ... see config/file-picker.php for the full list
],
```

### Route middleware

```php
'route_middleware' => ['web'],
```

The package registers a route at `/vendor/anil/livewire-file-picker/{file}` to serve CSS/JS assets. This route is protected by the middleware listed here.

## Customising Views

Publish the blade views to override the UI:

```bash
php artisan file-picker:install --views
# or
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

This package uses [PHPStan](https://phpstan.org/) with [Larastan](https://github.com/larastan/larastan) at **level 8**.

```bash
composer analyse
```

## Contributing

Contributions are welcome! Please open an issue or pull request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Er. Anil Kumar Thakur](https://github.com/anilkumarthakur60)
- [All Contributors](../../contributors)
