<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\DTOs;

use Anil\LivewireFilePicker\Enums\FileType;
use Anil\LivewireFilePicker\Support\ModelAttributeHelper;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class MediaItem implements Arrayable
{
    public function __construct(
        public int $id,
        public string $filename,
        public string $url,
        public ?string $thumbnailUrl,
        public int $size,
        public string $mimeType,
        public string $extension,
        public string $aggregateType,
        public ?string $alt,
        public FileType $fileType,
        public string $icon,
        public Carbon $createdAt,
        public ?int $width = null,
        public ?int $height = null,
        public ?int $duration = null,
    ) {}

    public static function fromMedia(Model $media): self
    {
        $fileType = self::resolveFileType($media);

        $url = self::resolveUrl($media);

        /** @var Carbon $createdAt */
        $createdAt = $media->getAttribute('created_at');

        return new self(
            id: ModelAttributeHelper::int($media, 'id'),
            filename: ModelAttributeHelper::string($media, 'filename'),
            url: $url,
            thumbnailUrl: $fileType === FileType::IMAGE ? $url : null,
            size: ModelAttributeHelper::int($media, 'size'),
            mimeType: ModelAttributeHelper::string($media, 'mime_type'),
            extension: ModelAttributeHelper::string($media, 'extension'),
            aggregateType: ModelAttributeHelper::string($media, 'aggregate_type', 'unknown'),
            alt: ModelAttributeHelper::nullableString($media, 'alt'),
            fileType: $fileType,
            icon: $fileType->icon(),
            createdAt: $createdAt,
            width: ModelAttributeHelper::nullableInt($media, 'width'),
            height: ModelAttributeHelper::nullableInt($media, 'height'),
            duration: ModelAttributeHelper::nullableInt($media, 'duration'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnailUrl,
            'size' => $this->size,
            'size_formatted' => $this->formattedSize(),
            'mime_type' => $this->mimeType,
            'extension' => $this->extension,
            'aggregate_type' => $this->aggregateType,
            'alt' => $this->alt,
            'file_type' => $this->fileType->value,
            'file_type_label' => $this->fileType->label(),
            'file_type_color' => $this->fileType->color(),
            'icon' => $this->icon,
            'created_at' => $this->createdAt,
            'created_at_formatted' => $this->createdAt->format('M j, Y'),
            'created_at_diff' => $this->createdAt->diffForHumans(),
            'width' => $this->width,
            'height' => $this->height,
            'dimensions' => $this->dimensions(),
            'duration' => $this->duration,
            'duration_formatted' => $this->formattedDuration(),
        ];
    }

    public function formattedSize(): string
    {
        return match (true) {
            $this->size >= 1_073_741_824 => number_format($this->size / 1_073_741_824, 2).' GB',
            $this->size >= 1_048_576 => number_format($this->size / 1_048_576, 2).' MB',
            $this->size >= 1_024 => number_format($this->size / 1_024, 2).' KB',
            default => $this->size.' bytes',
        };
    }

    public function dimensions(): ?string
    {
        if ($this->width !== null && $this->height !== null) {
            return "{$this->width} x {$this->height}";
        }

        return null;
    }

    public function formattedDuration(): ?string
    {
        if ($this->duration === null || $this->duration === 0) {
            return null;
        }

        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function isImage(): bool
    {
        return $this->fileType === FileType::IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->fileType === FileType::VIDEO;
    }

    public function isAudio(): bool
    {
        return $this->fileType === FileType::AUDIO;
    }

    public function isDocument(): bool
    {
        return $this->fileType === FileType::DOCUMENT;
    }

    private static function resolveUrl(Model $media): string
    {
        if (! method_exists($media, 'getUrl')) {
            return '';
        }

        $result = $media->getUrl();

        return is_string($result) ? $result : '';
    }

    private static function resolveFileType(Model $media): FileType
    {
        $mime = ModelAttributeHelper::string($media, 'mime_type');
        $aggregateType = ModelAttributeHelper::string($media, 'aggregate_type');

        if (str_contains($aggregateType, 'image') || str_starts_with($mime, 'image/')) {
            return FileType::IMAGE;
        }

        if (str_contains($aggregateType, 'video') || str_starts_with($mime, 'video/')) {
            return FileType::VIDEO;
        }

        if (str_contains($aggregateType, 'audio') || str_starts_with($mime, 'audio/')) {
            return FileType::AUDIO;
        }

        return FileType::fromExtension(ModelAttributeHelper::string($media, 'extension'));
    }
}
