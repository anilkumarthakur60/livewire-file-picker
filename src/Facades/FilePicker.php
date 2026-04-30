<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Facades;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * @method static Builder<Model> query()
 * @method static Builder<Model> queryWithTrashed()
 * @method static Builder<Model> queryOnlyTrashed()
 * @method static Model|null findById(int $id)
 * @method static Model findByIdOrFail(int $id)
 * @method static Collection<int, Model> findByIds(array<int> $ids)
 * @method static Model|null findByHash(string $hash)
 * @method static Model upload(TemporaryUploadedFile $file, array<string, mixed> $options = [])
 * @method static Model replaceFile(int $id, TemporaryUploadedFile $file, array<string, mixed> $options = [])
 * @method static bool delete(int $id)
 * @method static bool restore(int $id)
 * @method static bool forceDelete(int $id)
 * @method static int deleteMany(array<int> $ids)
 * @method static bool updateAlt(int $id, string $alt)
 * @method static bool rename(int $id, string $newFilename)
 * @method static bool toggleFavorite(int $id)
 * @method static bool setFavorite(int $id, bool $favorite)
 * @method static bool setTags(int $id, array<int, string> $tags)
 * @method static bool addTag(int $id, string $tag)
 * @method static bool removeTag(int $id, string $tag)
 * @method static bool moveToFolder(int $id, ?string $folder)
 * @method static int bulkMoveToFolder(array<int> $ids, ?string $folder)
 * @method static void incrementDownloadCount(int $id)
 * @method static array<int, string> getFolders()
 * @method static array<int, string> getAllTags()
 * @method static array<string, mixed> getStats()
 * @method static bool exists(int $id)
 * @method static array<string, mixed> transform(Model $media)
 * @method static string driverName()
 *
 * @see MediaDriverInterface
 */
final class FilePicker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MediaDriverInterface::class;
    }
}
