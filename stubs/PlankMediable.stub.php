<?php

/**
 * PHPStan stubs for plank/laravel-mediable.
 *
 * These stubs provide type information for PHPStan when
 * plank/laravel-mediable is not installed as a dependency.
 */

namespace Plank\Mediable;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public function getUrl(): string
    {
        return '';
    }
}

class MediaUploader
{
    public function fromSource(mixed $source): self
    {
        return $this;
    }

    public function toDestination(string $disk, string $directory): self
    {
        return $this;
    }

    public function useFilename(string $filename): self
    {
        return $this;
    }

    public function onDuplicateIncrement(): self
    {
        return $this;
    }

    public function makePublic(): self
    {
        return $this;
    }

    public function makePrivate(): self
    {
        return $this;
    }

    public function upload(): Media
    {
        return new Media;
    }
}
