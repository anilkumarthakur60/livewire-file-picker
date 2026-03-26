<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Support\DefaultAuthorization;

it('allows all actions by default', function (): void {
    $auth = new DefaultAuthorization;

    expect($auth->canUpload())->toBeTrue();
    expect($auth->canDelete(1))->toBeTrue();
    expect($auth->canViewLibrary())->toBeTrue();
    expect($auth->canEditAlt(1))->toBeTrue();
});
