<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Livewire;

use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;
use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Enums\FileType;
use Anil\LivewireFilePicker\Enums\SortDirection;
use Anil\LivewireFilePicker\Enums\SortField;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Anil\LivewireFilePicker\Traits\HandlesFileUpload;
use Anil\LivewireFilePicker\Traits\HandlesMediaQuery;
use Anil\LivewireFilePicker\Traits\HandlesPagination;
use Anil\LivewireFilePicker\Traits\HandlesSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FilePicker extends Component
{
    use HandlesFileUpload;
    use HandlesMediaQuery;
    use HandlesPagination;
    use HandlesSelection;

    // =====================================================================
    // Component Configuration (passed during mount)
    // =====================================================================

    public bool $multiple = false;

    public int $maxFiles = 10;

    /** @var array<int> */
    public array $selected = [];

    /** @var array<string> */
    public array $allowedTypes = [];

    public string $inputName = 'files';

    #[Locked]
    public string $inputId = '';

    public string $formId = '';

    public bool $autoSubmit = false;

    public string $callbackFunction = '';

    public string $buttonLabel = '';

    public bool $showPreview = true;

    // =====================================================================
    // Modal State
    // =====================================================================

    public bool $showModal = false;

    public string $currentTab = 'library';

    // =====================================================================
    // Upload State
    // =====================================================================

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploadedFiles = [];

    public bool $isUploading = false;

    public string $uploadMessage = '';

    public string $uploadStatus = '';

    // =====================================================================
    // Search, Filter & Sort State
    // =====================================================================

    public string $search = '';

    public string $filterType = 'all';

    /** @var array<string, mixed> */
    public array $customFilterValues = [];

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    // =====================================================================
    // Media Items State
    // =====================================================================

    /** @var array<int, array<string, mixed>> */
    public array $mediaItems = [];

    // =====================================================================
    // Editing State
    // =====================================================================

    public ?int $editingMediaId = null;

    public string $editingAlt = '';

    public ?int $renamingMediaId = null;

    public string $renamingFilename = '';

    // =====================================================================
    // Pagination State
    // =====================================================================

    public int $perPage = 24;

    public int $currentPage = 1;

    public int $totalItems = 0;

    public int $totalPages = 0;

    // =====================================================================
    // Refresh State
    // =====================================================================

    public bool $isRefreshing = false;

    public int $renderTimestamp = 0;

    // =====================================================================
    // Driver (resolved via DI)
    // =====================================================================

    /**
     * Resolve the media driver from the container (singleton).
     */
    public function driver(): MediaDriverInterface
    {
        return app(MediaDriverInterface::class);
    }

    /**
     * Resolve the authorization handler from the container.
     */
    public function authorization(): FilePickerAuthorizationInterface
    {
        return app(FilePickerAuthorizationInterface::class);
    }

    // =====================================================================
    // Lifecycle
    // =====================================================================

    /**
     * @param  array<int|string>|int|null  $selected
     * @param  array<string>  $allowedTypes
     */
    public function mount(
        bool $multiple = false,
        int $maxFiles = 10,
        array|int|null $selected = [],
        array $allowedTypes = [],
        string $inputName = 'files',
        string $inputId = '',
        string $formId = '',
        bool $autoSubmit = false,
        string $callbackFunction = '',
        string $buttonLabel = '',
        bool $showPreview = true,
        int $perPage = 24,
    ): void {
        $this->multiple = $multiple;
        $this->maxFiles = $maxFiles;
        $this->allowedTypes = $allowedTypes;
        $this->inputName = $inputName;
        $this->inputId = $inputId !== '' ? $inputId : 'file-picker-'.uniqid();
        $this->formId = $formId;
        $this->autoSubmit = $autoSubmit;
        $this->callbackFunction = $callbackFunction;
        $this->buttonLabel = $buttonLabel !== '' ? $buttonLabel : ($multiple ? 'Select Files' : 'Select File');
        $this->showPreview = $showPreview;
        $this->perPage = $perPage;

        $this->selected = $this->normalizeSelectedInput($selected);

        $this->initializeCustomFilterValues();
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'file-picker::file-picker';

        /** @var array<int, array<string, mixed>> $customFilters */
        $customFilters = config('file-picker.ui.custom_filters', []);

        return view($viewName, [
            'fileTypes' => $this->getAvailableFileTypes(),
            'customFilters' => $customFilters,
            'sortFields' => SortField::cases(),
            'sortDirections' => SortDirection::cases(),
        ]);
    }

    // =====================================================================
    // Modal
    // =====================================================================

    public function openModal(): void
    {
        if (! $this->authorization()->canViewLibrary()) {
            return;
        }

        $this->showModal = true;
        $this->currentTab = 'library';
        $this->resetPagination();
        $this->resetUploadState();
        $this->loadMediaItems();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetUploadState();
        $this->cancelEditing();
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    // =====================================================================
    // Media Loading
    // =====================================================================

    public function loadMediaItems(): void
    {
        $query = $this->buildMediaQuery();

        $this->calculatePagination($query->count());

        /** @var Collection<int, Model> $media */
        $media = $query
            ->skip($this->getPaginationOffset())
            ->take($this->perPage)
            ->get();

        $this->mediaItems = $this->transformMediaCollection($media);
    }

    public function refreshMedia(): void
    {
        $this->isRefreshing = true;
        $this->resetPagination();
        $this->loadMediaItems();
        $this->isRefreshing = false;
        $this->dispatch('media-refreshed');
    }

    // =====================================================================
    // Sorting
    // =====================================================================

    public function sort(string $field): void
    {
        $sortField = SortField::tryFrom($field);

        if ($sortField === null) {
            return;
        }

        if ($this->sortField === $field) {
            $currentDirection = SortDirection::tryFrom($this->sortDirection) ?? SortDirection::DESC;
            $this->sortDirection = $currentDirection->toggle()->value;
        } else {
            $this->sortField = $field;
            $this->sortDirection = SortDirection::DESC->value;
        }

        $this->resetPagination();
        $this->loadMediaItems();
    }

    // =====================================================================
    // Filter & Search Handlers
    // =====================================================================

    public function updatedSearch(): void
    {
        $this->resetPagination();
        $this->loadMediaItems();
    }

    public function updatedFilterType(): void
    {
        $this->resetPagination();
        $this->loadMediaItems();
    }

    public function updatedCustomFilterValues(): void
    {
        $this->resetPagination();
        $this->loadMediaItems();
    }

    public function updatedSortField(): void
    {
        $this->resetPagination();
        $this->loadMediaItems();
    }

    public function updatedSortDirection(): void
    {
        $this->resetPagination();
        $this->loadMediaItems();
    }

    // =====================================================================
    // Editing
    // =====================================================================

    public function startEditing(int $mediaId, ?string $alt = ''): void
    {
        $this->editingMediaId = $mediaId;
        $this->editingAlt = $alt ?? '';
    }

    public function saveAlt(): void
    {
        if ($this->editingMediaId !== null && trim($this->editingAlt) !== '') {
            if (! $this->authorization()->canEditAlt($this->editingMediaId)) {
                $this->cancelEditing();

                return;
            }

            $this->updateMediaAlt($this->editingMediaId, trim($this->editingAlt));
            $this->loadMediaItems();
        }

        $this->cancelEditing();
    }

    public function cancelEditing(): void
    {
        $this->editingMediaId = null;
        $this->editingAlt = '';
    }

    // =====================================================================
    // Renaming
    // =====================================================================

    public function startRenaming(int $mediaId, string $filename = ''): void
    {
        $this->renamingMediaId = $mediaId;
        $this->renamingFilename = $filename;
    }

    public function saveRename(): void
    {
        if ($this->renamingMediaId !== null && trim($this->renamingFilename) !== '') {
            $this->renameMedia($this->renamingMediaId, trim($this->renamingFilename));
            $this->loadMediaItems();
        }

        $this->cancelRenaming();
    }

    public function cancelRenaming(): void
    {
        $this->renamingMediaId = null;
        $this->renamingFilename = '';
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function deleteMedia(int $mediaId): void
    {
        if (! $this->authorization()->canDelete($mediaId)) {
            return;
        }

        $this->selected = array_values(array_diff($this->selected, [$mediaId]));

        try {
            $this->deleteMediaById($mediaId);
        } catch (MediaNotFoundException) {
            // Item already deleted, just continue
        }

        $this->loadMediaItems();
        $this->dispatch('media-deleted', mediaId: $mediaId);
    }

    /**
     * @param  array<int>  $mediaIds
     */
    public function bulkDelete(array $mediaIds): void
    {
        $this->selected = array_values(array_diff($this->selected, $mediaIds));

        $deleted = $this->deleteMediaByIds($mediaIds);

        $this->loadMediaItems();
        $this->dispatch('media-bulk-deleted', count: $deleted);
    }

    // =====================================================================
    // Selection
    // =====================================================================

    public function toggleSelection(int $mediaId): void
    {
        $this->normalizeSelected();

        if ($this->multiple) {
            $this->toggleMultipleSelection($mediaId);
        } else {
            $this->toggleSingleSelection($mediaId);
        }

        $this->renderTimestamp = time();
        $this->dispatch('selection-updated', selected: $this->selected);

        if (! $this->showModal) {
            $this->dispatchSelectionToParent();
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->renderTimestamp = time();
        $this->dispatch('selection-updated', selected: $this->selected);

        if (! $this->showModal) {
            $this->dispatchSelectionToParent();
        }
    }

    public function insertSelected(): void
    {
        $this->normalizeSelected();
        $this->dispatchSelectionToParent();
        $this->closeModal();
    }

    // =====================================================================
    // Computed Properties
    // =====================================================================

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function selectedMediaItems(): array
    {
        if ($this->selected === []) {
            return [];
        }

        $media = $this->getMediaByIds($this->selected);

        return $this->transformMediaCollection($media);
    }

    /**
     * @return array<string, mixed>|null
     */
    #[Computed]
    public function activeMediaItem(): ?array
    {
        $selectedItems = $this->selectedMediaItems();

        if ($selectedItems === []) {
            return null;
        }

        return end($selectedItems);
    }

    #[Computed]
    public function hasSelection(): bool
    {
        return $this->selected !== [];
    }

    #[Computed]
    public function selectionLabel(): string
    {
        $count = count($this->selected);

        return match (true) {
            $count === 0 => 'No items selected',
            $count === 1 => '1 item selected',
            default => "{$count} items selected",
        };
    }

    // =====================================================================
    // Event Listeners
    // =====================================================================

    #[On('refresh-file-picker')]
    public function handleRefresh(): void
    {
        $this->refreshMedia();
    }

    #[On('clearUploadMessage')]
    public function clearUploadMessage(): void
    {
        $this->uploadMessage = '';
        $this->uploadStatus = '';
    }

    // =====================================================================
    // Private Helpers
    // =====================================================================

    private function dispatchSelectionToParent(): void
    {
        $this->dispatch('filesSelected', selected: $this->selected, inputName: $this->inputName);

        $this->dispatch('file-picker-selected', [
            'selected' => $this->selected,
            'inputName' => $this->inputName,
            'inputId' => $this->inputId,
            'formId' => $this->formId,
            'multiple' => $this->multiple,
            'autoSubmit' => $this->autoSubmit,
            'callbackFunction' => $this->callbackFunction,
        ]);
    }

    /**
     * @param  array<int|string>|int|null  $selected
     * @return array<int>
     */
    private function normalizeSelectedInput(array|int|null $selected): array
    {
        if ($selected === null) {
            return [];
        }

        if (is_int($selected)) {
            return [$selected];
        }

        return array_map(intval(...), array_filter($selected));
    }

    private function initializeCustomFilterValues(): void
    {
        /** @var array<int, array<string, mixed>> $customFilters */
        $customFilters = config('file-picker.ui.custom_filters', []);

        foreach ($customFilters as $filter) {
            /** @var string $name */
            $name = $filter['name'] ?? '';

            if ($name !== '') {
                $this->customFilterValues[$name] = '';
            }
        }
    }

    /**
     * @return array<int, array{value: string, label: string, icon: string}>
     */
    private function getAvailableFileTypes(): array
    {
        $types = [
            ['value' => 'all', 'label' => 'All Files', 'icon' => FileType::ALL->icon()],
        ];

        /** @var array<string>|null $configFilterTypes */
        $configFilterTypes = config('file-picker.ui.filter_types');

        $activeFilter = $configFilterTypes
            ?? ($this->allowedTypes !== [] && ! in_array('all', $this->allowedTypes) ? $this->allowedTypes : null);

        $fileTypeCases = $activeFilter !== null
            ? array_filter(array_map(FileType::tryFrom(...), $activeFilter))
            : FileType::cases();

        foreach ($fileTypeCases as $fileType) {
            if ($fileType !== FileType::ALL) {
                $types[] = [
                    'value' => $fileType->value,
                    'label' => $fileType->label(),
                    'icon' => $fileType->icon(),
                ];
            }
        }

        return $types;
    }
}
