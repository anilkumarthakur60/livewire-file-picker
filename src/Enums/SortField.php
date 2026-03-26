<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Enums;

enum SortField: string
{
    case CREATED_AT = 'created_at';
    case FILENAME = 'filename';
    case SIZE = 'size';
    case EXTENSION = 'extension';

    public function label(): string
    {
        return match ($this) {
            self::CREATED_AT => 'Date',
            self::FILENAME => 'Name',
            self::SIZE => 'Size',
            self::EXTENSION => 'Type',
        };
    }
}
