<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Models\FilePickerMedia;

return [
    /*
    |--------------------------------------------------------------------------
    | Media Driver
    |--------------------------------------------------------------------------
    |
    | The driver determines how media is stored, queried, and managed.
    |
    | Supported: "plank" (default), or any class implementing MediaDriverInterface.
    |
    | "plank" — Built on top of plank/laravel-mediable. Wraps Plank's Media model
    |           with extra columns (folder, tags, favorites, alt, hash, etc.) via
    |           an additive migration. Set 'driver' to the FQCN of a custom driver
    |           class to use a different backend.
    |
    */

    'driver' => env('FILE_PICKER_DRIVER', 'plank'),

    /*
    |--------------------------------------------------------------------------
    | Driver Configuration
    |--------------------------------------------------------------------------
    |
    | Each driver can have its own model, disk, directory, and visibility.
    | You may also register custom drivers by using a fully-qualified class name
    | as the 'driver' value above.
    |
    */

    'drivers' => [
        'plank' => [
            'model' => FilePickerMedia::class,
            'disk' => env('FILE_PICKER_DISK', 'public'),
            'directory' => env('FILE_PICKER_DIRECTORY', 'media'),
            'visibility' => env('FILE_PICKER_VISIBILITY', 'public'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    |
    | Maximum file size in kilobytes. Default is 102400 KB (100 MB).
    |
    */

    'max_file_size' => env('FILE_PICKER_MAX_SIZE', 102400),

    /*
    |--------------------------------------------------------------------------
    | Default Component Settings
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'multiple' => false,
        'max_files' => 40,
        'per_page' => 24,
        'show_preview' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Use 'all' for no restrictions, or specify:
    | ['image', 'video', 'audio', 'document', 'spreadsheet', 'presentation', 'archive', 'code']
    |
    */

    'allowed_types' => ['all'],

    /*
    |--------------------------------------------------------------------------
    | File Type Extensions
    |--------------------------------------------------------------------------
    */

    'extensions' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'tiff', 'tif', 'avif'],
        'video' => ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'wmv', 'flv', 'm4v'],
        'audio' => ['mp3', 'wav', 'aac', 'ogg', 'flac', 'm4a', 'wma', 'aiff'],
        'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt', 'md', 'epub'],
        'spreadsheet' => ['xls', 'xlsx', 'csv', 'ods', 'numbers'],
        'presentation' => ['ppt', 'pptx', 'odp', 'key'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'],
        'code' => ['js', 'ts', 'php', 'html', 'css', 'scss', 'json', 'xml', 'yaml', 'yml', 'vue', 'jsx', 'tsx', 'py', 'rb', 'go', 'rs', 'java', 'c', 'cpp', 'h', 'sh', 'bash'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Sorting
    |--------------------------------------------------------------------------
    |
    | Default sort field and direction for the media library.
    | Supported fields: 'created_at', 'filename', 'size', 'extension'
    | Supported directions: 'asc', 'desc'
    |
    */

    'sorting' => [
        'field' => 'created_at',
        'direction' => 'desc',
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */

    'ui' => [
        // Modal style: 'fullscreen' | 'centered'
        'modal_style' => 'fullscreen',

        // Thumbnail height in pixels
        'thumbnail_height' => 150,

        // Show file type badges on items
        'show_type_badges' => true,

        // Show file size in item info
        'show_file_size' => true,

        // Show upload date in item info
        'show_date' => true,

        // Theme colors — fully customizable
        'colors' => [
            'primary' => '#0073aa',
            'primary_hover' => '#005a87',
            'danger' => '#ef4444',
            'success' => '#10b981',
            'warning' => '#f59e0b',
        ],

        // Appearance
        'font_family' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif",
        'border_radius' => 8, // px

        // Grid layout — columns per breakpoint
        'grid_columns_xs' => 2, // < 480px
        'grid_columns_sm' => 3, // 481–768px
        'grid_columns_md' => 4, // 769–1024px
        'grid_columns' => 6, // 1025–1535px (large / default desktop)
        'grid_columns_xl' => 8, // ≥ 1536px (extra large)
        'grid_gap' => 14, // px — gap between grid items

        // Sidebar
        'sidebar_width' => 300, // px

        // Overlay settings
        'backdrop_blur' => 12, // px
        'backdrop_opacity' => 0.6,
        'z_index' => 9999,

        // Upload area
        'upload_preview_size' => 120, // px — thumbnail size in upload preview grid
        'upload_area_max_height' => 400, // px — max height of upload pending area before scroll

        // Filter types shown in dropdown. Set to null to auto-detect from FileType enum.
        // Example: ['image', 'video', 'document'] — only these types appear in filter.
        'filter_types' => [
            'image',
            'document',
            'video',
            'audio',
            'spreadsheet',
            'presentation',
        ],

        // Custom filters shown in the media library toolbar.
        // The array defines the UI controls, but the actual query logic
        // is delegated to the class specified in 'custom_filter_class'.
        // Supported types: 'select', 'text', 'checkbox', 'date_range'
        'custom_filters' => [],

        // The class responsible for handling the custom filter data and applying it to the builder.
        // It must implement \Anil\LivewireFilePicker\Contracts\CustomFilter.
        // Example: 'custom_filter_class' => \App\Filters\MediaLibraryFilter::class,
        'custom_filter_class' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Toggles
    |--------------------------------------------------------------------------
    */

    'features' => [
        'upload' => true,
        'delete' => true,
        'bulk_delete' => true,
        'edit_alt' => true,
        'rename' => true,
        'replace' => true,
        'search' => true,
        'filter' => true,
        'sorting' => true,
        'drag_drop' => true,
        'refresh' => true,
        'keyboard_navigation' => true,
        'paste_upload' => true,
        'favorites' => true,
        'tags' => true,
        'folders' => true,
        'trash' => true,
        'download' => true,
        'bulk_download' => true,
        'stats' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    |
    | When enabled, files are hashed (SHA-256) on upload. If an identical file
    | already exists, the strategy decides what to do.
    |
    | Strategies:
    |   'reuse'  — Return the existing media record (no new upload).
    |   'reject' — Throw a DuplicateMediaException.
    |   'allow'  — Create a new record anyway (no deduplication).
    |
    */

    'duplicate_detection' => [
        'enabled' => true,
        'strategy' => env('FILE_PICKER_DUPLICATE_STRATEGY', 'reuse'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trash / Soft Delete
    |--------------------------------------------------------------------------
    |
    | When enabled, deleted media is soft-deleted (moved to trash) instead of
    | permanently removed. Files on disk are retained until force-deleted or
    | pruned via the `file-picker:prune-trash` console command.
    |
    | 'retention_days' — How many days trashed items are kept before being
    |                    eligible for the prune command. Set 0 to disable
    |                    automatic pruning.
    |
    */

    'trash' => [
        'enabled' => true,
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ownership
    |--------------------------------------------------------------------------
    |
    | When auto_assign is true, the authenticated user's ID is recorded on
    | upload via the user_id column. Combine with a custom authorization
    | class to scope library views per-user.
    |
    */

    'ownership' => [
        'auto_assign' => true,
        'scope_to_owner' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Quota
    |--------------------------------------------------------------------------
    |
    | Cap total storage usage in BYTES. 0 disables the cap.
    |   'global'   — across all media records.
    |   'per_user' — per authenticated user (requires user_id).
    |
    */

    'storage_quota' => [
        'global' => env('FILE_PICKER_QUOTA_GLOBAL', 0),
        'per_user' => env('FILE_PICKER_QUOTA_PER_USER', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization / Text Strings
    |--------------------------------------------------------------------------
    */

    'texts' => [
        'modal_title' => 'Media Library',
        'tab_upload' => 'Upload Files',
        'tab_library' => 'Media Library',
        'tab_trash' => 'Trash',
        'drop_zone' => 'Drop files here or click to upload',
        'drop_zone_hint' => 'Supports: Images, Videos, Documents, and more',
        'search_placeholder' => 'Search media...',
        'filter_label' => 'Filter by type:',
        'sort_label' => 'Sort by:',
        'no_items' => 'No media found',
        'no_items_hint' => 'Upload some files to get started',
        'no_trash' => 'Trash is empty',
        'upload_button' => 'Upload Files',
        'cancel_button' => 'Cancel',
        'insert_button' => 'Insert Selected',
        'clear_selection' => 'Clear',
        'refresh_button' => 'Refresh',
        'delete_confirm' => 'Are you sure you want to move this file to trash?',
        'force_delete_confirm' => 'Permanently delete this file? This cannot be undone.',
        'restore_confirm' => 'Restore this file from trash?',
        'bulk_delete_confirm' => 'Move the selected files to trash?',
        'delete_button' => 'Move to Trash',
        'force_delete_button' => 'Delete forever',
        'restore_button' => 'Restore',
        'bulk_delete_button' => 'Move selected to trash',
        'edit_alt_placeholder' => 'Enter alt text...',
        'edit_alt_help' => 'Press Enter to save',
        'rename_placeholder' => 'Enter new filename...',
        'rename_button' => 'Rename',
        'replace_button' => 'Replace File',
        'sidebar_title' => 'Attachment Details',
        'sidebar_empty' => 'Select a file to view details',
        'url_label' => 'File URL',
        'alt_label' => 'Alt Text',
        'alt_placeholder' => 'Click to add alt text...',
        'preview_title' => 'Selected',
        'remove_all' => 'Remove All',
        'favorite_button' => 'Favorite',
        'unfavorite_button' => 'Remove from favorites',
        'favorites_only' => 'Favorites only',
        'tags_label' => 'Tags',
        'tags_placeholder' => 'Add tag and press Enter',
        'folder_label' => 'Folder',
        'folder_root' => 'All folders',
        'folder_none' => '(no folder)',
        'move_to_folder' => 'Move to folder...',
        'download_button' => 'Download',
        'download_all_button' => 'Download as ZIP',
        'duplicate_message' => 'A duplicate file was detected and reused.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    */

    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | An optional class that implements FilePickerAuthorizationInterface.
    | When set, the file picker will check authorization before allowing
    | uploads, deletes, alt-text edits, and viewing the media library.
    |
    | Leave empty to allow all actions (default behavior).
    |
    | Example: 'authorization_class' => \App\Auth\MediaPickerAuthorization::class,
    |
    */

    'authorization_class' => '',
];
