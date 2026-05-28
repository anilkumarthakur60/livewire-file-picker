# Changelog

All notable changes to `anil/livewire-file-picker` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **BREAKING:** The standalone `default` driver, the `file_picker_media` table, and the standalone `FilePickerMedia` model have been removed. The package is now built on top of [`plank/laravel-mediable`](https://github.com/plank/laravel-mediable) (^6.0) as a hard dependency. `FilePickerMedia` is now a thin wrapper that extends `Plank\Mediable\Media`, and the package's columns (`folder`, `tags`, `is_favorite`, `hash`, `width`, `height`, `duration`, `user_id`, `download_count`, `custom_properties`, `deleted_at`) are added to Plank's `media` table via an additive migration. Users on a previous version need a data migration to move rows from `file_picker_media` → `media`.
- The `plank` driver no longer no-ops on `setTags`, `addTag`, `removeTag`, `setFavorite`, `moveToFolder`, `getFolders`, `getAllTags`, `findByHash`, `updateAlt`, `incrementDownloadCount`. All of these now work against the extended Plank model.
- `replaceFile` on the Plank driver now mutates in place (preserves the media ID) instead of delete-and-recreate.
- `moveToFolder` now physically moves the file via Plank's `$media->move()` and falls back to a metadata-only update when the file is missing on disk.
- Mobile/tablet UI: the detail sidebar is now a right-side Sheet (shadcn-style timing) at viewports ≤1024px, freeing the main grid from the inline column on iPad-portrait and similar.

### Removed
- `Anil\LivewireFilePicker\Drivers\DefaultDriver`
- `database/migrations/2024_01_01_000000_create_file_picker_media_table.php`
- `stubs/PlankMediable.stub.php` (no longer needed — Plank is now installed as a real dependency)
- `DriverNotFoundException::plankNotInstalled()` factory method

### Fixed
- `MediaDownloadController` and `PruneOrphansCommand` no longer reference the removed `default` driver config or the removed `path` column; both now use Plank's `getDiskPath()` API.

## [0.x] — Prior history

Earlier releases shipped a standalone driver (`DefaultDriver` + `file_picker_media` table) alongside the Plank driver. See git history for details.
