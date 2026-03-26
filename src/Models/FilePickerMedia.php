<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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
 * @property array<string, mixed>|null $custom_properties
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $aggregate_type
 */
final class FilePickerMedia extends Model
{
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
}
