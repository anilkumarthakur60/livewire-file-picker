<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Traits;

use Anil\LivewireFilePicker\Contracts\CustomFilter;
use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Enums\FileType;
use Anil\LivewireFilePicker\Enums\SortDirection;
use Anil\LivewireFilePicker\Enums\SortField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $search
 * @property string $filterType
 * @property string $sortField
 * @property string $sortDirection
 * @property array<string> $allowedTypes
 * @property array<string, mixed> $customFilterValues
 *
 * @method MediaDriverInterface driver()
 */
trait HandlesMediaQuery
{
    /**
     * Build the base media query with filters and sorting applied.
     *
     * @return Builder<Model>
     */
    protected function buildMediaQuery(): Builder
    {
        $query = $this->driver()->query();

        $this->applySearchFilter($query);
        $this->applyTypeFilter($query);
        $this->applyAllowedTypesFilter($query);
        $this->applyCustomFilters($query);
        $this->applySorting($query);

        return $query;
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applySearchFilter(Builder $query): void
    {
        $search = trim($this->search);

        if ($search === '') {
            return;
        }

        $searchTerm = '%'.$search.'%';

        $query->where(function (Builder $q) use ($searchTerm): void {
            $q->where('filename', 'like', $searchTerm)
                ->orWhere('alt', 'like', $searchTerm);
        });
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyTypeFilter(Builder $query): void
    {
        if ($this->filterType === 'all' || $this->filterType === '') {
            return;
        }

        $fileType = FileType::tryFrom($this->filterType);

        if ($fileType === null || $fileType === FileType::ALL) {
            return;
        }

        $extensions = $fileType->extensions();

        if ($extensions !== []) {
            $query->whereIn('extension', $extensions);
        }
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyAllowedTypesFilter(Builder $query): void
    {
        if ($this->allowedTypes === [] || in_array('all', $this->allowedTypes, true)) {
            return;
        }

        /** @var array<string> $allowedExtensions */
        $allowedExtensions = [];

        foreach ($this->allowedTypes as $type) {
            $fileType = FileType::tryFrom($type);

            if ($fileType !== null) {
                $allowedExtensions = [...$allowedExtensions, ...$fileType->extensions()];
            }
        }

        if ($allowedExtensions !== []) {
            $query->whereIn('extension', array_unique($allowedExtensions));
        }
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applyCustomFilters(Builder $query): void
    {
        /** @var array<int, array<string, mixed>> $customFilters */
        $customFilters = config('file-picker.ui.custom_filters', []);

        if ($customFilters === []) {
            return;
        }

        /** @var class-string<CustomFilter>|string $filterClass */
        $filterClass = config('file-picker.ui.custom_filter_class', '');

        if ($filterClass === '' || ! class_exists($filterClass)) {
            return;
        }

        $filterInstance = app($filterClass);

        if ($filterInstance instanceof CustomFilter) {
            $filterInstance->apply($query, $this->customFilterValues);
        }
    }

    /**
     * @param  Builder<Model>  $query
     */
    protected function applySorting(Builder $query): void
    {
        $sortField = SortField::tryFrom($this->sortField) ?? SortField::CREATED_AT;
        $sortDirection = SortDirection::tryFrom($this->sortDirection) ?? SortDirection::DESC;

        $query->orderBy($sortField->value, $sortDirection->value);
    }

    /**
     * @param  Collection<int, Model>  $media
     * @return array<int, array<string, mixed>>
     */
    protected function transformMediaCollection(Collection $media): array
    {
        $driver = $this->driver();

        /** @var array<int, array<string, mixed>> $result */
        $result = $media->map(fn (Model $item): array => $driver->transform($item))->toArray();

        return $result;
    }

    /**
     * @param  array<int>  $ids
     * @return Collection<int, Model>
     */
    protected function getMediaByIds(array $ids): Collection
    {
        return $this->driver()->findByIds($ids);
    }

    protected function mediaExists(int $id): bool
    {
        return $this->driver()->exists($id);
    }

    protected function deleteMediaById(int $id): bool
    {
        return $this->driver()->delete($id);
    }

    /**
     * @param  array<int>  $ids
     */
    protected function deleteMediaByIds(array $ids): int
    {
        return $this->driver()->deleteMany($ids);
    }

    protected function updateMediaAlt(int $id, string $alt): bool
    {
        return $this->driver()->updateAlt($id, $alt);
    }

    protected function renameMedia(int $id, string $newFilename): bool
    {
        return $this->driver()->rename($id, $newFilename);
    }
}
