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
        /** @var Carbon|null $deletedAt */
        $deletedAt = $media->getAttribute('deleted_at');

        $width = ModelAttributeHelper::nullableInt($media, 'width');
        $height = ModelAttributeHelper::nullableInt($media, 'height');

        return [
            'id' => ModelAttributeHelper::int($media, 'id'),
            'filename' => ModelAttributeHelper::string($media, 'filename'),
            'url' => $url,
            'thumbnail_url' => $fileType === FileType::IMAGE ? $url : null,
            'download_url' => $this->buildDownloadUrl($media),
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
            'duration' => ModelAttributeHelper::nullableInt($media, 'duration'),
            'duration_formatted' => $this->formatDuration(ModelAttributeHelper::nullableInt($media, 'duration')),
            'hash' => ModelAttributeHelper::nullableString($media, 'hash'),
            'folder' => ModelAttributeHelper::nullableString($media, 'folder'),
            'tags' => $this->extractTags($media),
            'is_favorite' => (bool) $media->getAttribute('is_favorite'),
            'user_id' => ModelAttributeHelper::nullableInt($media, 'user_id'),
            'download_count' => ModelAttributeHelper::int($media, 'download_count'),
            'deleted_at' => $deletedAt,
            'deleted_at_formatted' => $deletedAt?->format('M j, Y') ?? '',
            'is_trashed' => $deletedAt !== null,
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

    private function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null || $seconds <= 0) {
            return null;
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
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

    private function buildDownloadUrl(Model $media): string
    {
        $key = $media->getKey();

        if (! is_int($key) && ! is_numeric($key)) {
            return '';
        }

        try {
            return url('/file-picker/download/'.(int) $key);
        } catch (\Throwable) {
            return '';
        }
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
