<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Mediable\Media;

/**
 * @property int $id
 * @property string|null $disk
 * @property string|null $directory
 * @property string|null $filename
 * @property string|null $extension
 * @property string|null $mime_type
 * @property string|null $aggregate_type
 * @property int|null $size
 * @property string $alt
 * @property string|null $folder
 * @property array<int, string>|null $tags
 * @property bool $is_favorite
 * @property string|null $hash
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration
 * @property int|null $user_id
 * @property int $download_count
 * @property array<string, mixed>|null $custom_properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class FilePickerMedia extends Media
{
    use SoftDeletes;

    /** @var list<string> */
    protected $guarded = ['id'];

    /**
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('is_favorite', true);
    }

    /**
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeInFolder(Builder $query, ?string $folder): Builder
    {
        if ($folder === null || $folder === '') {
            return $query->whereNull('folder');
        }

        return $query->where('folder', $folder);
    }

    /**
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size'              => 'integer',
            'width'             => 'integer',
            'height'            => 'integer',
            'duration'          => 'integer',
            'is_favorite'       => 'boolean',
            'user_id'           => 'integer',
            'download_count'    => 'integer',
            'tags'              => 'array',
            'custom_properties' => 'array',
        ];
    }
}
