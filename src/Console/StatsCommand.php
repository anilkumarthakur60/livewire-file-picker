<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Console;

use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Illuminate\Console\Command;

final class StatsCommand extends Command
{
    /** @var string */
    protected $signature = 'file-picker:stats';

    /** @var string */
    protected $description = 'Print aggregate statistics about the media library.';

    public function handle(MediaDriverInterface $driver): int
    {
        $stats = $driver->getStats();

        $totalCount = is_int($stats['total_count'] ?? null) ? $stats['total_count'] : 0;
        $totalSize = is_int($stats['total_size'] ?? null) ? $stats['total_size'] : 0;
        $favorites = is_int($stats['favorites_count'] ?? null) ? $stats['favorites_count'] : 0;
        $trashed = is_int($stats['trashed_count'] ?? null) ? $stats['trashed_count'] : 0;
        $folders = is_int($stats['folders_count'] ?? null) ? $stats['folders_count'] : 0;
        $tagsCount = is_int($stats['tags_count'] ?? null) ? $stats['tags_count'] : 0;

        $this->info('Media library statistics');
        $this->line('---');
        $this->line('Total items     : ' . $totalCount);
        $this->line('Total size      : ' . $this->formatBytes($totalSize));
        $this->line('Favorites       : ' . $favorites);
        $this->line('Trashed         : ' . $trashed);
        $this->line('Folders in use  : ' . $folders);
        $this->line('Distinct tags   : ' . $tagsCount);
        $this->line('---');

        $byType = is_array($stats['by_type'] ?? null) ? $stats['by_type'] : [];

        if ($byType !== []) {
            $rows = [];

            foreach ($byType as $type => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $count = is_int($row['count'] ?? null) ? $row['count'] : 0;
                $size = is_int($row['size'] ?? null) ? $row['size'] : 0;

                $rows[] = [
                    is_string($type) ? $type : (string) $type,
                    (string) $count,
                    $this->formatBytes($size),
                ];
            }

            $this->table(['Type', 'Count', 'Size'], $rows);
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        return match (true) {
            $bytes >= 1_073_741_824 => number_format($bytes / 1_073_741_824, 2) . ' GB',
            $bytes >= 1_048_576     => number_format($bytes / 1_048_576, 2) . ' MB',
            $bytes >= 1_024         => number_format($bytes / 1_024, 2) . ' KB',
            default                 => $bytes . ' bytes',
        };
    }
}
