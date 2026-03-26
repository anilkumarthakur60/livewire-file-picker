<div class="fp-component" wire:key="file-picker-{{ $inputId }}">
    <button type="button" wire:click="openModal" class="fp-trigger">
        <svg class="fp-trigger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
            </path>
        </svg>
        <span>{{ $buttonLabel }}</span>
        @if (!empty($selected))
            <span class="fp-trigger-badge">{{ count($selected) }}</span>
        @endif
    </button>

    @if ($showModal)
        @teleport('body')
        <div class="fp-component">
            <div class="fp-backdrop" wire:click.self="closeModal" x-data x-trap.noscroll="true">
                <div class="fp-modal" @click.stop>
                    {{-- ============================================================
                         HEADER
                         ============================================================ --}}
                    <div class="fp-header">
                        <div class="fp-header-left">
                            <h2 class="fp-title">{{ config('file-picker.texts.modal_title', 'Media Library') }}</h2>
                            <div class="fp-modal-tabs">
                                @if (config('file-picker.features.upload', true))
                                    <button type="button" wire:click="setTab('upload')"
                                            class="fp-tab {{ $currentTab === 'upload' ? 'fp-tab-active' : '' }}">
                                        {{ config('file-picker.texts.tab_upload', 'Upload Files') }}
                                    </button>
                                @endif
                                <button type="button" wire:click="setTab('library')"
                                        class="fp-tab {{ $currentTab === 'library' ? 'fp-tab-active' : '' }}">
                                    {{ config('file-picker.texts.tab_library', 'Media Library') }}
                                </button>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" class="fp-close">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- ============================================================
                         TOOLBAR (Library tab only)
                         ============================================================ --}}
                    @if($currentTab === 'library')
                        <div class="fp-toolbar">
                            {{-- ── Row 1: Type filter pills ─────────────────────────── --}}
                            @if (config('file-picker.features.filter', true))
                                <div class="fp-toolbar-row fp-toolbar-filters">
                                    <div class="fp-type-pills" role="tablist">
                                        @foreach ($fileTypes as $type)
                                            <button type="button"
                                                    wire:click="$set('filterType', '{{ $type['value'] }}')"
                                                    class="fp-type-pill {{ $filterType === $type['value'] ? 'fp-type-pill-active' : '' }}"
                                                    role="tab"
                                                    aria-selected="{{ $filterType === $type['value'] ? 'true' : 'false' }}">
                                                <svg class="fp-type-pill-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $type['icon'] }}"></path>
                                                </svg>
                                                <span>{{ $type['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>

                                    {{-- Item count badge --}}
                                    <div class="fp-toolbar-meta">
                                        <span class="fp-item-count">
                                            {{ $totalItems }} {{ $totalItems === 1 ? 'item' : 'items' }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- ── Row 2: Search + Sort + Custom filters + Actions ──── --}}
                            <div class="fp-toolbar-row fp-toolbar-actions">
                                {{-- Search --}}
                                @if (config('file-picker.features.search', true))
                                    <div class="fp-search-wrap" x-data="{ focused: false }">
                                        <svg class="fp-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text"
                                               wire:model.live.debounce.300ms="search"
                                               placeholder="{{ config('file-picker.texts.search_placeholder', 'Search media...') }}"
                                               class="fp-search-input"
                                               @focus="focused = true"
                                               @blur="focused = false">
                                        @if ($search !== '')
                                            <button type="button"
                                                    wire:click="$set('search', '')"
                                                    class="fp-search-clear"
                                                    title="Clear search">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                {{-- Separator --}}
                                <div class="fp-toolbar-separator"></div>

                                {{-- Custom Filters --}}
                                @foreach ($customFilters ?? [] as $cf)
                                    <div class="fp-toolbar-control">
                                        @if (($cf['type'] ?? '') === 'select')
                                            <select wire:model.live="customFilterValues.{{ $cf['name'] }}" class="fp-select" title="{{ $cf['label'] ?? '' }}">
                                                @foreach ($cf['options'] ?? [] as $val => $optLabel)
                                                    <option value="{{ $val }}">{{ $optLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif (($cf['type'] ?? '') === 'text')
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="customFilterValues.{{ $cf['name'] }}"
                                                   placeholder="{{ $cf['placeholder'] ?? $cf['label'] ?? '' }}"
                                                   class="fp-toolbar-text-input">
                                        @elseif (($cf['type'] ?? '') === 'checkbox')
                                            <label class="fp-toolbar-toggle">
                                                <input type="checkbox" wire:model.live="customFilterValues.{{ $cf['name'] }}">
                                                <span>{{ $cf['label'] ?? '' }}</span>
                                            </label>
                                        @elseif (($cf['type'] ?? '') === 'date_range')
                                            <input type="date"
                                                   wire:model.live="customFilterValues.{{ $cf['name'] }}"
                                                   class="fp-toolbar-date-input"
                                                   title="{{ $cf['label'] ?? '' }}">
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Sort --}}
                                @if (config('file-picker.features.sorting', true))
                                    <div class="fp-sort-group">
                                        <select wire:model.live="sortField" class="fp-select fp-select-compact" title="{{ config('file-picker.texts.sort_label', 'Sort by') }}">
                                            @foreach ($sortFields as $sf)
                                                <option value="{{ $sf->value }}">{{ $sf->label() }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button"
                                                wire:click="sort('{{ $sortField }}')"
                                                class="fp-sort-dir"
                                                title="{{ $sortDirection === 'asc' ? 'Ascending' : 'Descending' }}">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="{{ $sortDirection === 'asc' ? 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12' : 'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4' }}"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif

                                {{-- Refresh --}}
                                @if (config('file-picker.features.refresh', true))
                                    <button type="button"
                                            wire:click="refreshMedia"
                                            class="fp-toolbar-icon-btn"
                                            title="{{ config('file-picker.texts.refresh_button', 'Refresh') }}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                             class="{{ $isRefreshing ? 'fp-spin' : '' }}">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                @endif

                                {{-- Upload shortcut --}}
                                @if (config('file-picker.features.upload', true))
                                    <button type="button"
                                            wire:click="setTab('upload')"
                                            class="fp-toolbar-upload-btn"
                                            title="{{ config('file-picker.texts.tab_upload', 'Upload Files') }}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        <span>Upload</span>
                                    </button>
                                @endif
                            </div>

                            {{-- ── Row 3: Selection action bar (visible when items selected) ── --}}
                            @if (!empty($selected))
                                <div class="fp-toolbar-row fp-toolbar-selection">
                                    <div class="fp-selection-bar">
                                        <div class="fp-selection-info-bar">
                                            <span class="fp-selection-check">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </span>
                                            <span class="fp-selection-count">{{ $this->selectionLabel }}</span>
                                            @if ($multiple && $maxFiles > 1)
                                                <span class="fp-selection-remaining">({{ $this->remainingSelections }} remaining)</span>
                                            @endif
                                        </div>
                                        <div class="fp-selection-actions">
                                            <button type="button" wire:click="clearSelection" class="fp-selection-action-btn">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                {{ config('file-picker.texts.clear_selection', 'Clear') }}
                                            </button>
                                            @if (config('file-picker.features.bulk_delete', true) && config('file-picker.features.delete', true))
                                                <button type="button"
                                                        wire:click="bulkDelete({{ json_encode($selected) }})"
                                                        wire:confirm="{{ config('file-picker.texts.bulk_delete_confirm', 'Are you sure you want to delete the selected files?') }}"
                                                        class="fp-selection-action-btn fp-selection-action-danger">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    {{ config('file-picker.texts.bulk_delete_button', 'Delete selected') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- ============================================================
                         CONTENT
                         ============================================================ --}}
                    <div class="fp-content">

                        {{-- ========================================================
                             UPLOAD TAB
                             ======================================================== --}}
                        @if ($currentTab === 'upload' && config('file-picker.features.upload', true))
                            <div class="fp-upload-area" id="fp-upload-area" x-data="{
                                isDragging: false,
                                handleDrop(e) {
                                    this.isDragging = false;
                                    const files = e.dataTransfer.files;
                                    const fileInput = document.getElementById('fp-file-input-{{ $inputId }}');
                                    if (fileInput && files.length > 0) {
                                        fileInput.files = files;
                                        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            }">
                                {{-- Upload Message (toast at top) --}}
                                @if ($uploadMessage)
                                    <div class="fp-upload-toast">
                                        <div class="fp-upload-message fp-upload-message-{{ $uploadStatus }}">
                                            @if ($uploadStatus === 'success')
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            @elseif ($uploadStatus === 'error')
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            @else
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                            @endif
                                            <span>{{ $uploadMessage }}</span>
                                            <button type="button" wire:click="$dispatch('clearUploadMessage')" class="fp-upload-toast-close">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Scrollable body: dropzone + pending files --}}
                                <div class="fp-upload-body">
                                    @if (empty($uploadedFiles))
                                        {{-- Empty state: large centered dropzone --}}
                                        <div class="fp-dropzone fp-dropzone-hero"
                                             @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="handleDrop($event)"
                                             @click="$refs.fileInput.click()"
                                             :class="{ 'fp-dropzone-active': isDragging }">
                                            <div class="fp-dropzone-icon-wrap">
                                                <svg class="fp-dropzone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                </svg>
                                            </div>
                                            <p class="fp-dropzone-text">{{ config('file-picker.texts.drop_zone', 'Drop files here or click to upload') }}</p>
                                            <p class="fp-dropzone-hint">{{ config('file-picker.texts.drop_zone_hint', 'Supports: Images, Videos, Documents, and more') }}</p>
                                            <div class="fp-dropzone-browse">
                                                <span class="fp-dropzone-browse-btn">Browse Files</span>
                                            </div>
                                            <input type="file" wire:model="uploadedFiles" multiple
                                                   id="fp-file-input-{{ $inputId }}"
                                                   x-ref="fileInput"
                                                   accept="{{ $this->getAcceptAttribute() }}"
                                                   class="fp-file-input">
                                        </div>
                                    @else
                                        {{-- Compact dropzone when files are queued --}}
                                        <div class="fp-dropzone fp-dropzone-compact"
                                             @dragover.prevent="isDragging = true"
                                             @dragleave.prevent="isDragging = false"
                                             @drop.prevent="handleDrop($event)"
                                             @click="$refs.fileInput.click()"
                                             :class="{ 'fp-dropzone-active': isDragging }">
                                            <svg class="fp-dropzone-compact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span class="fp-dropzone-compact-text">Add more files</span>
                                            <input type="file" wire:model="uploadedFiles" multiple
                                                   id="fp-file-input-{{ $inputId }}"
                                                   x-ref="fileInput"
                                                   accept="{{ $this->getAcceptAttribute() }}"
                                                   class="fp-file-input">
                                        </div>

                                        {{-- Pending file grid --}}
                                        <div class="fp-pending-grid">
                                            @php
                                                $totalSize = 0;
                                                foreach ($uploadedFiles as $f) { $totalSize += $f->getSize(); }
                                            @endphp
                                            @foreach ($uploadedFiles as $index => $file)
                                                @php
                                                    $isImage = str_starts_with($file->getMimeType() ?? '', 'image/');
                                                    $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                                                    $fileSize = $file->getSize();
                                                @endphp
                                                <div class="fp-pending-card" wire:key="pending-{{ $index }}">
                                                    {{-- Thumbnail --}}
                                                    <div class="fp-pending-thumb">
                                                        @if ($isImage)
                                                            <img src="{{ $file->temporaryUrl() }}"
                                                                 alt="{{ $file->getClientOriginalName() }}">
                                                        @else
                                                            <div class="fp-pending-thumb-icon">
                                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                          stroke-width="1.5"
                                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                                </svg>
                                                                <span class="fp-pending-thumb-ext">.{{ $ext }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    {{-- Info --}}
                                                    <div class="fp-pending-card-info">
                                                        <div class="fp-pending-name" title="{{ $file->getClientOriginalName() }}">
                                                            {{ \Illuminate\Support\Str::limit($file->getClientOriginalName(), 18) }}
                                                        </div>
                                                        <div class="fp-pending-size">
                                                            @if ($fileSize >= 1_048_576)
                                                                {{ number_format($fileSize / 1_048_576, 1) }} MB
                                                            @else
                                                                {{ number_format($fileSize / 1024, 1) }} KB
                                                            @endif
                                                        </div>
                                                    </div>
                                                    {{-- Remove --}}
                                                    <button type="button" wire:click="removePendingFile({{ $index }})"
                                                            class="fp-pending-remove" title="Remove">
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Sticky action bar (always visible when files queued) --}}
                                @if (!empty($uploadedFiles))
                                    <div class="fp-upload-action-bar">
                                        <div class="fp-upload-action-info">
                                            <span class="fp-upload-action-count">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ count($uploadedFiles) }} {{ count($uploadedFiles) === 1 ? 'file' : 'files' }} ready
                                            </span>
                                            <span class="fp-upload-action-size">
                                                @if ($totalSize >= 1_048_576)
                                                    {{ number_format($totalSize / 1_048_576, 1) }} MB total
                                                @else
                                                    {{ number_format($totalSize / 1024, 1) }} KB total
                                                @endif
                                            </span>
                                        </div>
                                        <div class="fp-upload-action-buttons">
                                            <button type="button"
                                                    wire:click="$set('uploadedFiles', [])"
                                                    class="fp-upload-action-clear">
                                                Clear All
                                            </button>
                                            <button type="button"
                                                    wire:click="uploadFiles"
                                                    class="fp-upload-action-submit"
                                                    @disabled($isUploading)>
                                                @if ($isUploading)
                                                    <svg class="fp-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Uploading...
                                                @else
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                    {{ config('file-picker.texts.upload_button', 'Upload Files') }}
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- ========================================================
                             MEDIA LIBRARY TAB
                             ======================================================== --}}
                        @if ($currentTab === 'library')
                            <div class="fp-library-layout">
                                <div class="fp-library-main">
                                    <div class="fp-grid" wire:key="media-grid-{{ $renderTimestamp }}">
                                        @forelse ($mediaItems as $item)
                                            <div
                                                class="fp-item {{ $this->isItemSelected($item['id']) ? 'fp-item-selected' : '' }}"
                                                wire:click="toggleSelection({{ $item['id'] }})"
                                                wire:key="media-item-{{ $item['id'] }}-{{ $renderTimestamp }}"
                                                title="{{ $item['filename'] }}">

                                                {{-- Selection Indicator --}}
                                                <div class="fp-item-select">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>

                                                {{-- Thumbnail / File Icon --}}
                                                <div class="fp-thumbnail">
                                                    @if ($item['thumbnail_url'])
                                                        <img src="{{ $item['thumbnail_url'] }}"
                                                             alt="{{ $item['alt'] ?? $item['filename'] }}"
                                                             loading="lazy">
                                                    @elseif ($item['file_type'] === 'video')
                                                        <div class="fp-file-icon">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="1.5" d="{{ $item['icon'] }}"></path>
                                                            </svg>
                                                            <span class="fp-file-ext">.{{ $item['extension'] }}</span>
                                                        </div>
                                                        <div class="fp-video-overlay">
                                                            <div class="fp-play-icon">
                                                                <svg fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M8 5v14l11-7z"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="fp-file-icon">
                                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="1.5" d="{{ $item['icon'] }}"></path>
                                                            </svg>
                                                            <span class="fp-file-ext">.{{ $item['extension'] }}</span>
                                                        </div>
                                                    @endif

                                                    @if (config('file-picker.ui.show_type_badges', true) && $item['file_type'] !== 'all')
                                                        <span class="fp-type-badge"
                                                              style="background: {{ $item['file_type_color'] }}">{{ $item['file_type_label'] }}</span>
                                                    @endif
                                                </div>

                                                {{-- Info --}}
                                                <div class="fp-item-info">
                                                    <div
                                                        class="fp-item-name">{{ \Illuminate\Support\Str::limit($item['filename'], 20) }}</div>
                                                    <div class="fp-item-meta">
                                                        @if (config('file-picker.ui.show_file_size', true))
                                                            <span
                                                                class="fp-item-size">{{ $item['size_formatted'] }}</span>
                                                        @endif
                                                        @if (config('file-picker.ui.show_date', true) && !empty($item['created_at_formatted']))
                                                            <span
                                                                class="fp-item-date">{{ $item['created_at_formatted'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="fp-empty" style="grid-column: 1 / -1;">
                                                <svg class="fp-empty-icon" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="1.5"
                                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                <p class="fp-empty-title">{{ config('file-picker.texts.no_items', 'No media found') }}</p>
                                                <p class="fp-empty-hint">{{ config('file-picker.texts.no_items_hint', 'Upload some files to get started') }}</p>
                                                @if (config('file-picker.features.upload', true))
                                                    <button type="button" wire:click="setTab('upload')"
                                                            class="fp-btn fp-btn-primary"
                                                            style="margin-top: 16px; font-size: 13px; padding: 8px 18px;">
                                                        {{ config('file-picker.texts.upload_button', 'Upload Files') }}
                                                    </button>
                                                @endif
                                            </div>
                                        @endforelse
                                    </div>

                                    {{-- Pagination --}}
                                    @if ($totalPages > 1)
                                        <div class="fp-pagination">
                                            <div class="fp-pagination-info">{{ $this->paginationInfo }}</div>
                                            <div class="fp-pagination-controls">
                                                <button type="button" wire:click="previousPage"
                                                        class="fp-page-btn" @disabled($currentPage <= 1)>
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                    </svg>
                                                </button>
                                                <div class="fp-page-numbers">
                                                    @foreach ($this->visiblePages as $page)
                                                        @if ($page === '...')
                                                            <span class="fp-page-ellipsis">...</span>
                                                        @else
                                                            <button type="button" wire:click="goToPage({{ $page }})"
                                                                    class="fp-page-num {{ $page === $currentPage ? 'fp-page-active' : '' }}">{{ $page }}</button>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <button type="button" wire:click="nextPage"
                                                        class="fp-page-btn" @disabled($currentPage >= $totalPages)>
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Sidebar: Attachment Details --}}
                                <div class="fp-library-sidebar">
                                    @if ($this->activeMediaItem)
                                        @php $active = $this->activeMediaItem; @endphp
                                        <h3 class="fp-sidebar-title">{{ config('file-picker.texts.sidebar_title', 'Attachment Details') }}</h3>

                                        {{-- Thumbnail Preview --}}
                                        <div class="fp-sidebar-thumb">
                                            @if ($active['thumbnail_url'])
                                                <img src="{{ $active['thumbnail_url'] }}"
                                                     alt="{{ $active['alt'] ?? $active['filename'] }}">
                                            @elseif ($active['file_type'] === 'video' && $active['url'])
                                                <video src="{{ $active['url'] }}" style="width:100%;max-height:200px"
                                                       controls></video>
                                            @else
                                                <div class="fp-file-icon" style="height:120px">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                         style="width:40px;height:40px">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="1.5" d="{{ $active['icon'] }}"></path>
                                                    </svg>
                                                    <span class="fp-file-ext">.{{ $active['extension'] }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Meta Info --}}
                                        <div class="fp-sidebar-meta">
                                            <div class="fp-meta-filename">{{ $active['filename'] }}
                                                .{{ $active['extension'] }}</div>
                                            @if (config('file-picker.ui.show_date', true))
                                                <div>{{ $active['created_at_formatted'] }}</div>
                                            @endif
                                            @if (config('file-picker.ui.show_file_size', true))
                                                <div>{{ $active['size_formatted'] }}</div>
                                            @endif
                                            @if ($active['dimensions'])
                                                <div>{{ $active['dimensions'] }}</div>
                                            @endif
                                            @if ($active['duration_formatted'] ?? null)
                                                <div>Duration: {{ $active['duration_formatted'] }}</div>
                                            @endif
                                        </div>

                                        <hr class="fp-sidebar-divider">

                                        {{-- URL --}}
                                        <div class="fp-input-group">
                                            <label>{{ config('file-picker.texts.url_label', 'File URL') }}</label>
                                            <input type="text" value="{{ $active['url'] }}" class="fp-readonly-input"
                                                   readonly onclick="this.select()">
                                        </div>

                                        {{-- Alt Text --}}
                                        @if (config('file-picker.features.edit_alt', true))
                                            <div class="fp-input-group">
                                                <label>{{ config('file-picker.texts.alt_label', 'Alt Text') }}</label>
                                                @if ($editingMediaId === $active['id'])
                                                    <input type="text" wire:model="editingAlt"
                                                           wire:keydown.enter="saveAlt"
                                                           placeholder="{{ config('file-picker.texts.edit_alt_placeholder', 'Enter alt text...') }}"
                                                           class="fp-search-input"
                                                           style="width:100%;background-image:none;padding-left:10px">
                                                    <p class="fp-input-help">{{ config('file-picker.texts.edit_alt_help', 'Press Enter to save') }}</p>
                                                @else
                                                    <div class="fp-alt-display"
                                                         wire:click="startEditing({{ $active['id'] }}, '{{ addslashes($active['alt'] ?? '') }}')">
                                                        {{ $active['alt'] ?: config('file-picker.texts.alt_placeholder', 'Click to add alt text...') }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Delete --}}
                                        @if (config('file-picker.features.delete', true))
                                            <div class="fp-sidebar-actions">
                                                <button type="button"
                                                        wire:click="deleteMedia({{ $active['id'] }})"
                                                        wire:confirm="{{ config('file-picker.texts.delete_confirm', 'Are you sure you want to delete this file?') }}"
                                                        class="fp-btn-link fp-text-danger">
                                                    {{ config('file-picker.texts.delete_button', 'Delete permanently') }}
                                                </button>
                                            </div>
                                        @endif
                                    @else
                                        <div class="fp-sidebar-empty">
                                            <p>{{ config('file-picker.texts.sidebar_empty', 'Select a file to view details') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ============================================================
                         FOOTER
                         ============================================================ --}}
                    <div class="fp-footer">
                        <div class="fp-footer-left">
                            <span class="fp-selection-info">{{ $this->selectionLabel }}</span>
                            @if (!empty($selected))
                                <button type="button" wire:click="clearSelection" class="fp-clear-btn">
                                    {{ config('file-picker.texts.clear_selection', 'Clear') }}
                                </button>
                            @endif
                        </div>
                        <div class="fp-footer-right">
                            <button type="button" wire:click="closeModal" class="fp-btn fp-btn-secondary">
                                {{ config('file-picker.texts.cancel_button', 'Cancel') }}
                            </button>
                            <button type="button" wire:click="insertSelected"
                                    class="fp-btn fp-btn-primary" @disabled(empty($selected))>
                                {{ config('file-picker.texts.insert_button', 'Insert Selected') }}
                                @if (!empty($selected))
                                    <span class="fp-insert-count">({{ count($selected) }})</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- Hidden Inputs for Form Integration --}}
    <div class="fp-hidden-inputs">
        @foreach ($selected as $id)
            <input type="hidden" name="{{ $inputName }}{{ $multiple ? '[]' : '' }}" value="{{ $id }}"
                   @if($formId) form="{{ $formId }}" @endif>
        @endforeach
    </div>

    {{-- Preview Area --}}
    @if ($showPreview && !empty($selected))
        <div class="fp-preview">
            <div class="fp-preview-header">
                <h3 class="fp-preview-title">{{ config('file-picker.texts.preview_title', 'Selected') }}
                    ({{ count($selected) }})</h3>
                <button type="button" wire:click="clearSelection" class="fp-preview-remove-all">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    {{ config('file-picker.texts.remove_all', 'Remove All') }}
                </button>
            </div>
            <div class="fp-preview-grid">
                @foreach ($this->selectedMediaItems as $item)
                    <div class="fp-preview-item">
                        @if ($item['thumbnail_url'])
                            <img src="{{ $item['thumbnail_url'] }}" alt="{{ $item['alt'] ?? $item['filename'] }}">
                        @elseif ($item['file_type'] === 'video')
                            <video src="{{ $item['url'] }}" class="fp-preview-video"></video>
                        @else
                            <div class="fp-preview-file">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="{{ $item['icon'] }}"></path>
                                </svg>
                            </div>
                        @endif
                        <span class="fp-preview-name">{{ \Illuminate\Support\Str::limit($item['filename'], 15) }}</span>
                        <button type="button" wire:click="toggleSelection({{ $item['id'] }})" class="fp-preview-remove">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

@once
    @push('head')
        <link rel="stylesheet" href="{{ asset('vendor/anil/livewire-file-picker/file-picker.css') }}">
    @endpush
@endonce

@once
    @push('scripts')
        <script src="{{ asset('vendor/anil/livewire-file-picker/file-picker.js') }}" defer></script>
    @endpush
@endonce
