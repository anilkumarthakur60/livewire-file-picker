<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $filename
 * @property string $disk
 * @property string $directory
 * @property string $path
 * @property string $extension
 * @property string $mime_type
 * @property int $size
 * @property string|null $alt
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration
 * @property string|null $hash
 * @property string|null $folder
 * @property array<int, string>|null $tags
 * @property bool $is_favorite
 * @property int|null $user_id
 * @property int $download_count
 * @property array<string, mixed>|null $custom_properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read string $aggregate_type
 */
final class FilePickerMedia extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'file_picker_media';

    /** @var list<string> */
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'is_favorite' => 'boolean',
            'user_id' => 'integer',
            'download_count' => 'integer',
            'tags' => 'array',
            'custom_properties' => 'array',
        ];
    }

    public function getUrl(): string
    {
        /** @var string $disk */
        $disk = $this->disk ?? config('file-picker.drivers.default.disk', 'public');

        /** @var FilesystemAdapter $diskInstance */
        $diskInstance = Storage::disk($disk);

        return $diskInstance->url($this->path);
    }

    public function getFullPath(): string
    {
        /** @var string $disk */
        $disk = $this->disk ?? config('file-picker.drivers.default.disk', 'public');

        /** @var FilesystemAdapter $diskInstance */
        $diskInstance = Storage::disk($disk);

        return $diskInstance->path($this->path);
    }

    public function getAggregateTypeAttribute(): string
    {
        $mime = $this->mime_type ?? '';

        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            default => 'document',
        };
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeFavorites(Builder $query): Builder
    {
        return $query->where('is_favorite', true);
    }

    /**
     * @param  Builder<self>  $query
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
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
