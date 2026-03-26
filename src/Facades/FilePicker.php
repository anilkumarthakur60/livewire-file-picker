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
 * @method static Model|null findById(int $id)
 * @method static Collection<int, Model> findByIds(array<int> $ids)
 * @method static Model upload(TemporaryUploadedFile $file, array<string, mixed> $options = [])
 * @method static bool delete(int $id)
 * @method static int deleteMany(array<int> $ids)
 * @method static bool updateAlt(int $id, string $alt)
 * @method static bool rename(int $id, string $newFilename)
 * @method static bool exists(int $id)
 * @method static array<string, mixed> transform(Model $media)
 * @method static string driverName()
 * @method static Model findByIdOrFail(int $id)
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
