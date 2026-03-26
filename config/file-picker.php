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
    | Supported: "default", "plank", or any class implementing MediaDriverInterface.
    |
    | "default" — Uses the package's built-in model & migration. Zero external deps.
    | "plank"   — Uses plank/laravel-mediable (must be installed separately).
    |
    */

    'driver' => env('FILE_PICKER_DRIVER', 'default'),

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
        'default' => [
            'model' => FilePickerMedia::class,
            'disk' => env('FILE_PICKER_DISK', 'public'),
            'directory' => env('FILE_PICKER_DIRECTORY', 'media'),
            'visibility' => env('FILE_PICKER_VISIBILITY', 'public'),
        ],

        'plank' => [
            'model' => 'Plank\Mediable\Media',
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
        'max_files' => 10,
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

        // Grid layout
        'grid_min_width' => 160, // px — min column width in media grid
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
        //
        // Example UI setup:
        // 'custom_filters' => [
        //     [
        //         'name'        => 'tag',
        //         'label'       => 'Tag',
        //         'type'        => 'select',
        //         'placeholder' => 'All Tags',
        //         'options'     => ['' => 'All Tags', 'nature' => 'Nature', 'urban' => 'Urban'],
        //     ],
        //     [
        //         'name'        => 'featured',
        //         'label'       => 'Featured Only',
        //         'type'        => 'checkbox',
        //     ],
        // ],
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
        'search' => true,
        'filter' => true,
        'sorting' => true,
        'drag_drop' => true,
        'refresh' => true,
        'keyboard_navigation' => true,
        'paste_upload' => true,
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
        'drop_zone' => 'Drop files here or click to upload',
        'drop_zone_hint' => 'Supports: Images, Videos, Documents, and more',
        'search_placeholder' => 'Search media...',
        'filter_label' => 'Filter by type:',
        'sort_label' => 'Sort by:',
        'no_items' => 'No media found',
        'no_items_hint' => 'Upload some files to get started',
        'upload_button' => 'Upload Files',
        'cancel_button' => 'Cancel',
        'insert_button' => 'Insert Selected',
        'clear_selection' => 'Clear',
        'refresh_button' => 'Refresh',
        'delete_confirm' => 'Are you sure you want to delete this file?',
        'bulk_delete_confirm' => 'Are you sure you want to delete the selected files?',
        'delete_button' => 'Delete permanently',
        'bulk_delete_button' => 'Delete selected',
        'edit_alt_placeholder' => 'Enter alt text...',
        'edit_alt_help' => 'Press Enter to save',
        'rename_placeholder' => 'Enter new filename...',
        'rename_button' => 'Rename',
        'sidebar_title' => 'Attachment Details',
        'sidebar_empty' => 'Select a file to view details',
        'url_label' => 'File URL',
        'alt_label' => 'Alt Text',
        'alt_placeholder' => 'Click to add alt text...',
        'preview_title' => 'Selected',
        'remove_all' => 'Remove All',
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
