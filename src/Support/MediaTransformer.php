<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Support;

use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Enums\FileType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

final class MediaTransformer implements MediaTransformerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function transform(Model $media): array
    {
        $fileType = $this->resolveFileType($media);
        $url = $this->resolveUrl($media);
        $size = ModelAttributeHelper::int($media, 'size');

        /** @var Carbon|null $createdAt */
        $createdAt = $media->getAttribute('created_at');

        $width = ModelAttributeHelper::nullableInt($media, 'width');
        $height = ModelAttributeHelper::nullableInt($media, 'height');

        return [
            'id' => ModelAttributeHelper::int($media, 'id'),
            'filename' => ModelAttributeHelper::string($media, 'filename'),
            'url' => $url,
            'thumbnail_url' => $fileType === FileType::IMAGE ? $url : null,
            'size' => $size,
            'size_formatted' => $this->formatSize($size),
            'mime_type' => ModelAttributeHelper::string($media, 'mime_type'),
            'extension' => ModelAttributeHelper::string($media, 'extension'),
            'aggregate_type' => ModelAttributeHelper::string($media, 'aggregate_type', 'document'),
            'alt' => ModelAttributeHelper::nullableString($media, 'alt'),
            'file_type' => $fileType->value,
            'file_type_label' => $fileType->label(),
            'file_type_color' => $fileType->color(),
            'icon' => $fileType->icon(),
            'created_at' => $createdAt,
            'created_at_formatted' => $createdAt?->format('M j, Y') ?? '',
            'created_at_diff' => $createdAt?->diffForHumans() ?? '',
            'width' => $width,
            'height' => $height,
            'dimensions' => ($width !== null && $height !== null) ? "{$width} x {$height}" : null,
        ];
    }

    public function formatSize(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2).' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    private function resolveFileType(Model $media): FileType
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

        $extension = ModelAttributeHelper::string($media, 'extension');

        return FileType::fromExtension($extension);
    }

    private function resolveUrl(Model $media): string
    {
        if (! method_exists($media, 'getUrl')) {
            return '';
        }

        $result = $media->getUrl();

        return is_string($result) ? $result : '';
    }
}
