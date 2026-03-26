<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class MediaDeleted
{
    use Dispatchable;

    public function __construct(
        public int $mediaId,
        public string $driver,
    ) {}
}
