<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class MediaTagged
{
    use Dispatchable;

    /**
     * @param  array<int, string>  $tags
     */
    public function __construct(
        public int $mediaId,
        public array $tags,
        public string $driver,
    ) {}
}
