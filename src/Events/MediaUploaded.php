<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class MediaUploaded
{
    use Dispatchable;

    public function __construct(
        public Model $media,
        public string $driver,
    ) {}
}
