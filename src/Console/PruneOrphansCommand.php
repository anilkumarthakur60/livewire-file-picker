<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Console;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class PruneOrphansCommand extends Command
{
    /** @var string */
    protected $signature = 'file-picker:prune-orphans
        {--dry-run : Show what would be deleted without deleting}';

    /** @var string */
    protected $description = 'Find and remove orphaned media records (DB rows whose underlying file is missing).';

    public function handle(MediaDriverInterface $driver): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $orphaned = 0;

        $driver->queryWithTrashed()->lazyById()->each(function (Model $media) use ($driver, $dryRun, &$orphaned): void {
            $disk = $this->resolveDisk($media);
            $rawPath = $media->getAttribute('path');
            $path = is_string($rawPath) ? $rawPath : '';

            if ($path === '') {
                return;
            }

            if (Storage::disk($disk)->exists($path)) {
                return;
            }

            $orphaned++;
            $key = $media->getKey();
            $keyString = is_int($key) || is_numeric($key) ? (string) $key : '?';

            $this->line("Orphan: id={$keyString} disk={$disk} path={$path}");

            if ($dryRun) {
                return;
            }

            if (is_int($key) || is_numeric($key)) {
                try {
                    $driver->forceDelete((int) $key);
                } catch (\Throwable $e) {
                    $this->error('Could not remove orphan #'.$keyString.': '.$e->getMessage());
                }
            }
        });

        $this->info(($dryRun ? 'Would remove ' : 'Removed ').$orphaned.' orphan record(s).');

        return self::SUCCESS;
    }

    private function resolveDisk(Model $media): string
    {
        $disk = $media->getAttribute('disk');

        if (is_string($disk) && $disk !== '') {
            return $disk;
        }

        /** @var string $configured */
        $configured = config('file-picker.drivers.default.disk', 'public');

        return $configured;
    }
}
