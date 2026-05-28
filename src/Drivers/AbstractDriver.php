<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Drivers;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Events\MediaDeleted;
use Anil\LivewireFilePicker\Events\MediaFavoriteToggled;
use Anil\LivewireFilePicker\Events\MediaMovedToFolder;
use Anil\LivewireFilePicker\Events\MediaRenamed;
use Anil\LivewireFilePicker\Events\MediaRestored;
use Anil\LivewireFilePicker\Events\MediaTagged;
use Anil\LivewireFilePicker\Exceptions\MediaNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

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

    /**
     * @return Builder<Model>
     */
    public function queryWithTrashed(): Builder
    {
        $query = $this->query();

        if ($this->modelUsesSoftDeletes()) {
            return $query->withoutGlobalScope(SoftDeletingScope::class);
        }

        return $query;
    }

    /**
     * @return Builder<Model>
     */
    public function queryOnlyTrashed(): Builder
    {
        $query = $this->query();

        if ($this->modelUsesSoftDeletes()) {
            return $query
                ->withoutGlobalScope(SoftDeletingScope::class)
                ->whereNotNull($this->deletedAtColumn());
        }

        return $query->whereRaw('1 = 0');
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
     * @param array<int> $ids
     *
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

    public function findByHash(string $hash): ?Model
    {
        if ($hash === '') {
            return null;
        }

        return $this->queryWithTrashed()->where('hash', $hash)->first();
    }

    public function delete(int $id): bool
    {
        $media = $this->findByIdOrFail($id);

        if ($this->modelUsesSoftDeletes()) {
            $media->delete();
        } else {
            $this->performDelete($media);
        }

        MediaDeleted::dispatch($id, $this->driverName());

        return true;
    }

    public function restore(int $id): bool
    {
        if (! $this->modelUsesSoftDeletes()) {
            return false;
        }

        /** @var Model|null $media */
        $media = $this->queryOnlyTrashed()->where('id', $id)->first();

        if ($media === null) {
            throw MediaNotFoundException::withId($id);
        }

        if (method_exists($media, 'restore')) {
            $media->restore();
        }

        MediaRestored::dispatch($media, $this->driverName());

        return true;
    }

    public function forceDelete(int $id): bool
    {
        /** @var Model|null $media */
        $media = $this->queryWithTrashed()->where('id', $id)->first();

        if ($media === null) {
            throw MediaNotFoundException::withId($id);
        }

        $this->performDelete($media);

        MediaDeleted::dispatch($id, $this->driverName());

        return true;
    }

    /**
     * @param array<int> $ids
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

    public function toggleFavorite(int $id): bool
    {
        $media = $this->findByIdOrFail($id);
        $current = (bool) $media->getAttribute('is_favorite');
        $next = ! $current;

        $media->setAttribute('is_favorite', $next);
        $media->save();

        MediaFavoriteToggled::dispatch($id, $next, $this->driverName());

        return $next;
    }

    public function setFavorite(int $id, bool $favorite): bool
    {
        $media = $this->findByIdOrFail($id);
        $media->setAttribute('is_favorite', $favorite);
        $media->save();

        MediaFavoriteToggled::dispatch($id, $favorite, $this->driverName());

        return true;
    }

    /**
     * @param array<int, string> $tags
     */
    public function setTags(int $id, array $tags): bool
    {
        $media = $this->findByIdOrFail($id);

        $clean = array_values(array_unique(array_filter(array_map(
            fn (string $t): string => trim($t),
            $tags,
        ), fn (string $t): bool => $t !== '')));

        $media->setAttribute('tags', $clean);
        $media->save();

        MediaTagged::dispatch($id, $clean, $this->driverName());

        return true;
    }

    public function addTag(int $id, string $tag): bool
    {
        $tag = trim($tag);

        if ($tag === '') {
            return false;
        }

        $media = $this->findByIdOrFail($id);

        /** @var array<int, string> $existing */
        $existing = $this->extractTags($media);

        if (in_array($tag, $existing, true)) {
            return false;
        }

        $existing[] = $tag;

        return $this->setTags($id, $existing);
    }

    public function removeTag(int $id, string $tag): bool
    {
        $media = $this->findByIdOrFail($id);

        /** @var array<int, string> $existing */
        $existing = $this->extractTags($media);

        $filtered = array_values(array_filter($existing, fn (string $t): bool => $t !== $tag));

        if (count($filtered) === count($existing)) {
            return false;
        }

        return $this->setTags($id, $filtered);
    }

    public function moveToFolder(int $id, ?string $folder): bool
    {
        $media = $this->findByIdOrFail($id);

        $oldFolder = $media->getAttribute('folder');
        $oldFolderString = is_string($oldFolder) ? $oldFolder : null;

        $newFolder = $folder !== null && trim($folder) !== '' ? trim($folder) : null;

        if ($oldFolderString === $newFolder) {
            return false;
        }

        $media->setAttribute('folder', $newFolder);
        $media->save();

        MediaMovedToFolder::dispatch($id, $oldFolderString, $newFolder, $this->driverName());

        return true;
    }

    /**
     * @param array<int> $ids
     */
    public function bulkMoveToFolder(array $ids, ?string $folder): int
    {
        $moved = 0;

        foreach ($ids as $id) {
            try {
                if ($this->moveToFolder($id, $folder)) {
                    $moved++;
                }
            } catch (MediaNotFoundException) {
                // skip
            }
        }

        return $moved;
    }

    public function incrementDownloadCount(int $id): void
    {
        $this->query()->where('id', $id)->increment('download_count');
    }

    /**
     * @return array<int, string>
     */
    public function getFolders(): array
    {
        /** @var array<int, string> $folders */
        $folders = $this->query()
            ->whereNotNull('folder')
            ->where('folder', '!=', '')
            ->distinct()
            ->orderBy('folder')
            ->pluck('folder')
            ->filter(fn (mixed $f): bool => is_string($f) && $f !== '')
            ->values()
            ->all();

        return $folders;
    }

    /**
     * @return array<int, string>
     */
    public function getAllTags(): array
    {
        /** @var array<int, string> $allTags */
        $allTags = [];

        $this->query()
            ->whereNotNull('tags')
            ->select(['id', 'tags'])
            ->chunkById(500, function ($items) use (&$allTags): void {
                foreach ($items as $item) {
                    $tags = $item->getAttribute('tags');

                    if (is_string($tags)) {
                        $decoded = json_decode($tags, true);
                        $tags = is_array($decoded) ? $decoded : [];
                    }

                    if (! is_array($tags)) {
                        continue;
                    }

                    foreach ($tags as $t) {
                        if (is_string($t) && $t !== '') {
                            $allTags[] = $t;
                        }
                    }
                }
            });

        return array_values(array_unique($allTags));
    }

    /**
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        $base = $this->query();

        $byType = [];

        /** @var array<int, object{aggregate_type: string|null, total: int, total_size: int}> $rows */
        $rows = $this->query()
            ->select(
                DB::raw("CASE
                    WHEN mime_type LIKE 'image/%' THEN 'image'
                    WHEN mime_type LIKE 'video/%' THEN 'video'
                    WHEN mime_type LIKE 'audio/%' THEN 'audio'
                    ELSE 'document'
                END as aggregate_type"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(size) as total_size'),
            )
            ->groupBy('aggregate_type')
            ->get()
            ->all();

        foreach ($rows as $row) {
            $type = is_string($row->aggregate_type) ? $row->aggregate_type : 'document';
            $byType[$type] = [
                'count' => (int) $row->total,
                'size'  => (int) $row->total_size,
            ];
        }

        $totalCount = (int) $base->count();
        $totalSize = (int) ((clone $base)->sum('size'));
        $favorites = (int) ((clone $base)->where('is_favorite', true)->count());
        $trashed = $this->modelUsesSoftDeletes() ? (int) $this->queryOnlyTrashed()->count() : 0;

        return [
            'total_count'     => $totalCount,
            'total_size'      => $totalSize,
            'favorites_count' => $favorites,
            'trashed_count'   => $trashed,
            'folders_count'   => count($this->getFolders()),
            'tags_count'      => count($this->getAllTags()),
            'by_type'         => $byType,
        ];
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

    protected function deletedAtColumn(): string
    {
        $instance = new ($this->modelClass());

        if (method_exists($instance, 'getDeletedAtColumn')) {
            $column = $instance->getDeletedAtColumn();

            if (is_string($column) && $column !== '') {
                return $column;
            }
        }

        return 'deleted_at';
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

    /**
     * Determine whether the underlying model uses Laravel's SoftDeletes trait.
     */
    protected function modelUsesSoftDeletes(): bool
    {
        $class = $this->modelClass();

        return in_array(SoftDeletes::class, $this->classUsesRecursive($class), true);
    }

    /**
     * @return array<int, string>
     */
    private function classUsesRecursive(string $class): array
    {
        $traits = [];

        foreach (array_reverse(class_parents($class) ?: []) + [$class => $class] as $cls) {
            $traits = array_merge($traits, class_uses($cls) ?: []);
        }

        $resolved = $traits;

        foreach ($traits as $trait) {
            $resolved = array_merge($resolved, class_uses($trait) ?: []);
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @return array<int, string>
     */
    private function extractTags(Model $media): array
    {
        $tags = $media->getAttribute('tags');

        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($tags)) {
            return [];
        }

        $clean = [];

        foreach ($tags as $tag) {
            if (is_string($tag) && $tag !== '') {
                $clean[] = $tag;
            }
        }

        return array_values(array_unique($clean));
    }
}
