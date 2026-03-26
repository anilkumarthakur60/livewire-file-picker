<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Enums;

enum SortDirection: string
{
    case ASC = 'asc';
    case DESC = 'desc';

    public function toggle(): self
    {
        return match ($this) {
            self::ASC => self::DESC,
            self::DESC => self::ASC,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ASC => 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12',
            self::DESC => 'M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4',
        };
    }
}
