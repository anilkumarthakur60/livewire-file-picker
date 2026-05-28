<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class InstallCommand extends Command
{
    protected $signature = 'file-picker:install
                            {--force : Overwrite existing published files}
                            {--no-migrate : Skip running migrations}
                            {--views : Also publish views (for customisation)}
                            {--assets : Also publish CSS/JS assets to public/ (not required — served via route)}
                            {--lang : Also publish language files}';

    protected $description = 'Install the Livewire File Picker package (publish config, run migrations)';

    public function handle(): int
    {
        $this->components->info('Installing Livewire File Picker...');

        $this->publishConfig();
        $this->publishMigrations();
        $this->runMigrations();

        if ($this->option('views')) {
            $this->publishViews();
        }

        if ($this->option('assets')) {
            $this->publishAssets();
        }

        if ($this->option('lang')) {
            $this->publishLang();
        }

        $this->newLine();
        $this->components->info('Livewire File Picker installed successfully.');
        $this->printNextSteps();

        return self::SUCCESS;
    }

    private function publishConfig(): void
    {
        $target = config_path('file-picker.php');
        $force = $this->option('force');

        if (File::exists($target) && ! $force) {
            $this->components->twoColumnDetail(
                '<fg=yellow>config/file-picker.php</>',
                '<fg=yellow>SKIPPED</> (already exists, use --force to overwrite)',
            );

            return;
        }

        $this->callSilently('vendor:publish', [
            '--tag'   => 'file-picker-config',
            '--force' => $force,
        ]);

        $this->components->twoColumnDetail('config/file-picker.php', '<fg=green>PUBLISHED</>');
    }

    private function publishMigrations(): void
    {
        $force = $this->option('force');

        $this->callSilently('vendor:publish', [
            '--tag'   => 'file-picker-migrations',
            '--force' => $force,
        ]);

        $this->components->twoColumnDetail('database/migrations/file-picker', '<fg=green>PUBLISHED</>');
    }

    private function runMigrations(): void
    {
        if ($this->option('no-migrate')) {
            $this->components->twoColumnDetail('Migrations', '<fg=yellow>SKIPPED</> (--no-migrate)');

            return;
        }

        $this->components->task('Running migrations', function (): void {
            $this->callSilently('migrate');
        });
    }

    private function publishViews(): void
    {
        $force = $this->option('force');

        $this->callSilently('vendor:publish', [
            '--tag'   => 'file-picker-views',
            '--force' => $force,
        ]);

        $this->components->twoColumnDetail(
            'resources/views/vendor/file-picker',
            '<fg=green>PUBLISHED</>',
        );
    }

    private function publishAssets(): void
    {
        $force = $this->option('force');

        $this->callSilently('vendor:publish', [
            '--tag'   => 'file-picker-assets',
            '--force' => $force,
        ]);

        $this->components->twoColumnDetail(
            'public/vendor/anil/livewire-file-picker',
            '<fg=green>PUBLISHED</>',
        );
    }

    private function publishLang(): void
    {
        $force = $this->option('force');

        $this->callSilently('vendor:publish', [
            '--tag'   => 'file-picker-lang',
            '--force' => $force,
        ]);

        $this->components->twoColumnDetail(
            'lang/vendor/file-picker',
            '<fg=green>PUBLISHED</>',
        );
    }

    private function printNextSteps(): void
    {
        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('  1. Review <fg=cyan>config/file-picker.php</> and set your preferred driver, disk, and directory.');
        $this->line('  2. Add <fg=cyan>@stack(\'head\')</> inside your layout\'s <fg=cyan><head></> and <fg=cyan>@stack(\'scripts\')</> before <fg=cyan></body></>.');
        $this->line('  3. Use the component: <fg=cyan><livewire:file-picker input-name="media" />');
        $this->newLine();
        $this->line('  Optional publishes:');
        $this->line('    <fg=yellow>--views</>   Publish blade views to customise the UI');
        $this->line('    <fg=yellow>--lang</>    Publish language files to override text');
        $this->line('    <fg=yellow>--assets</>  Publish CSS/JS to public/ (not required — served via route by default)');
    }
}
