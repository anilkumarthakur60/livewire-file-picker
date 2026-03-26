<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Contracts;

use Illuminate\Database\Eloquent\Model;

interface MediaTransformerInterface
{
    /**
     * Transform a Model into a normalized array for the UI.
     *
     * @return array<string, mixed>
     */
    public function transform(Model $media): array;

    /**
     * Format bytes into a human-readable string.
     */
    public function formatSize(int $bytes): string;
}
