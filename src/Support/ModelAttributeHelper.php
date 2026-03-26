<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Helper for extracting typed attribute values from Eloquent models.
 *
 * Model::getAttribute() returns mixed. At PHPStan level 10, casting mixed
 * directly is not allowed. This helper performs proper type narrowing.
 */
final class ModelAttributeHelper
{
    public static function string(Model $model, string $key, string $default = ''): string
    {
        $value = $model->getAttribute($key);

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }

    public static function int(Model $model, string $key, int $default = 0): int
    {
        $value = $model->getAttribute($key);

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    public static function nullableInt(Model $model, string $key): ?int
    {
        $value = $model->getAttribute($key);

        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    public static function nullableString(Model $model, string $key): ?string
    {
        $value = $model->getAttribute($key);

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }
}
