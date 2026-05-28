<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Console;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class PruneTrashCommand extends Command
{
    /** @var string */
    protected $signature = 'file-picker:prune-trash
        {--days= : Override the configured retention days}
        {--dry-run : Show what would be deleted without deleting}';

    /** @var string */
    protected $description = 'Permanently delete trashed media items older than the configured retention period.';

    public function handle(MediaDriverInterface $driver): int
    {
        $optionDays = $this->option('days');
        /** @var int $configDays */
        $configDays = config('file-picker.trash.retention_days', 30);

        $days = is_numeric($optionDays) ? (int) $optionDays : $configDays;

        if ($days <= 0) {
            $this->info('Trash pruning disabled (retention_days = 0).');

            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subDays($days);

        $query = $driver->queryOnlyTrashed()->where('deleted_at', '<=', $cutoff);
        $count = (int) $query->count();

        if ($count === 0) {
            $this->info('No trashed items older than ' . $days . ' days found.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} trashed items older than {$days} days.");

        if ((bool) $this->option('dry-run')) {
            $this->warn('Dry run — nothing was deleted.');

            return self::SUCCESS;
        }

        $deleted = 0;

        $query->lazyById()->each(function (Model $media) use ($driver, &$deleted): void {
            $key = $media->getKey();

            if (is_int($key) || is_numeric($key)) {
                try {
                    $driver->forceDelete((int) $key);
                    $deleted++;
                } catch (Throwable $e) {
                    $this->error('Could not delete media #' . $key . ': ' . $e->getMessage());
                }
            }
        });

        $this->info("Permanently deleted {$deleted} items.");

        return self::SUCCESS;
    }
}
