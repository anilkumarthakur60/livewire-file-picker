<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaDeleted;
use Anil\LivewireFilePicker\Events\MediaRenamed;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractDriver implements MediaDriverInterface
{
    public function __construct(
        protected readonly MediaTransformerInterface $transformer,
    ) {}

    /**
     * @return Builder<Model>
     */
    public function query(): Builder
    {
        return $this->modelClass()::query();
    }

    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->findById($id) ?? throw MediaNotFoundException::withId($id);
    }

    /**
     * @param  array<int>  $ids
     * @return Collection<int, Model>
     */
    public function findByIds(array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        $idsAsInts = array_map(intval(...), $ids);

        /** @var Collection<int, Model> $items */
        $items = $this->query()
            ->whereIn('id', $idsAsInts)
            ->get();

        // Preserve the requested order by sorting in PHP
        // (works across all database drivers, unlike MySQL's FIELD())
        $idOrder = array_flip($idsAsInts);

        /** @var Collection<int, Model> $sorted */
        $sorted = $items->sort(function (Model $a, Model $b) use ($idOrder): int {
            $keyA = $a->getKey();
            $keyB = $b->getKey();
            $posA = $idOrder[is_int($keyA) ? $keyA : (is_numeric($keyA) ? (int) $keyA : 0)] ?? PHP_INT_MAX;
            $posB = $idOrder[is_int($keyB) ? $keyB : (is_numeric($keyB) ? (int) $keyB : 0)] ?? PHP_INT_MAX;

            return $posA <=> $posB;
        })->values();

        return $sorted;
    }

    public function delete(int $id): bool
    {
        $media = $this->findByIdOrFail($id);

        $this->performDelete($media);

        MediaDeleted::dispatch($id, $this->driverName());

        return true;
    }

    /**
     * @param  array<int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        $deleted = 0;

        foreach ($ids as $id) {
            try {
                $this->delete($id);
                $deleted++;
            } catch (MediaNotFoundException) {
                // Skip items that no longer exist
            }
        }

        return $deleted;
    }

    public function updateAlt(int $id, string $alt): bool
    {
        $media = $this->findByIdOrFail($id);
        $media->setAttribute('alt', $alt);
        $media->save();

        return true;
    }

    public function rename(int $id, string $newFilename): bool
    {
        $media = $this->findByIdOrFail($id);
        $oldFilename = is_string($media->getAttribute('filename')) ? $media->getAttribute('filename') : '';
        $media->setAttribute('filename', $newFilename);
        $media->save();

        MediaRenamed::dispatch($id, $oldFilename, $newFilename, $this->driverName());

        return true;
    }

    public function exists(int $id): bool
    {
        return $this->query()->where('id', $id)->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(Model $media): array
    {
        return $this->transformer->transform($media);
    }

    /**
     * Get the model class for this driver.
     *
     * @return class-string<Model>
     */
    abstract protected function modelClass(): string;

    /**
     * Perform driver-specific delete logic (e.g. remove file from disk).
     */
    abstract protected function performDelete(Model $media): void;
}
