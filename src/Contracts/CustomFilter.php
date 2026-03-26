<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface CustomFilter
{
    /**
     * Apply the custom filter logic to the query.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $values
     */
    public function apply(Builder $query, array $values): void;
}
