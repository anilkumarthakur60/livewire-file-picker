<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Enums\SortDirection;
use Anil\LivewireFilePicker\Enums\SortField;

it('toggles sort direction', function (): void {
    expect(SortDirection::ASC->toggle())->toBe(SortDirection::DESC);
    expect(SortDirection::DESC->toggle())->toBe(SortDirection::ASC);
});

it('has icons for sort directions', function (): void {
    expect(SortDirection::ASC->icon())->toBeString()->not->toBeEmpty();
    expect(SortDirection::DESC->icon())->toBeString()->not->toBeEmpty();
});

it('has labels for sort fields', function (): void {
    foreach (SortField::cases() as $field) {
        expect($field->label())->toBeString()->not->toBeEmpty();
    }
});
