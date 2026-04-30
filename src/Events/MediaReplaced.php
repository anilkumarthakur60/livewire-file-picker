<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class MediaReplaced
{
    use Dispatchable;

    public function __construct(
        public Model $media,
        public string $oldPath,
        public string $newPath,
        public string $driver,
    ) {}
}
