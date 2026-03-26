@push('styles')
<style>
    /* ===================================================================
       FILE PICKER — DYNAMIC DESIGN TOKENS (from config/file-picker.php)
       =================================================================== */
    .fp-component {
        /* Brand Colors */
        --fp-config-primary: {{ config('file-picker.ui.colors.primary', '#0073aa') }};
        --fp-config-primary-hover: {{ config('file-picker.ui.colors.primary_hover', '#005a87') }};
        --fp-config-danger: {{ config('file-picker.ui.colors.danger', '#ef4444') }};
        --fp-config-success: {{ config('file-picker.ui.colors.success', '#10b981') }};
        --fp-config-warning: {{ config('file-picker.ui.colors.warning', '#f59e0b') }};

        /* Typography */
        --fp-config-font-family: {{ config('file-picker.ui.font_family', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif") }};

        /* Dimensions */
        --fp-config-thumbnail-height: {{ config('file-picker.ui.thumbnail_height', 150) }}px;
        --fp-config-radius: {{ config('file-picker.ui.border_radius', 8) }}px;
        --fp-config-grid-min-width: {{ config('file-picker.ui.grid_min_width', 160) }}px;
        --fp-config-grid-gap: {{ config('file-picker.ui.grid_gap', 14) }}px;
        --fp-config-sidebar-width: {{ config('file-picker.ui.sidebar_width', 300) }}px;

        /* Overlay */
        --fp-config-backdrop-blur: {{ config('file-picker.ui.backdrop_blur', 12) }}px;
        --fp-config-backdrop-opacity: {{ config('file-picker.ui.backdrop_opacity', 0.6) }};
        --fp-config-z-index: {{ config('file-picker.ui.z_index', 9999) }};

        /* Upload */
        --fp-config-upload-grid-min-width: {{ config('file-picker.ui.upload_preview_size', 120) }}px;
        --fp-config-upload-max-height: {{ config('file-picker.ui.upload_area_max_height', 400) }}px;
    }
</style>
@endpush
