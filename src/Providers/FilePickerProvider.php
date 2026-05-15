<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Providers;

use Anil\LivewireFilePicker\Commands\InstallCommand;
use Anil\LivewireFilePicker\Console\PruneOrphansCommand;
use Anil\LivewireFilePicker\Console\PruneTrashCommand;
use Anil\LivewireFilePicker\Console\StatsCommand;
use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;
use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Contracts\MediaTransformerInterface;
use Anil\LivewireFilePicker\Drivers\DefaultDriver;
use Anil\LivewireFilePicker\Drivers\PlankMediaDriver;
use Anil\LivewireFilePicker\Exceptions\DriverNotFoundException;
use Anil\LivewireFilePicker\Livewire\FilePicker;
use Anil\LivewireFilePicker\Support\DefaultAuthorization;
use Anil\LivewireFilePicker\Support\MediaTransformer;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class FilePickerProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerLivewireComponents();
        $this->registerViews();
        $this->registerTranslations();
        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerMigrations();
        $this->registerCommands();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/file-picker.php',
            'file-picker'
        );

        $this->app->singleton(MediaTransformerInterface::class, MediaTransformer::class);

        $this->app->singleton(FilePickerAuthorizationInterface::class, function (): FilePickerAuthorizationInterface {
            /** @var class-string<FilePickerAuthorizationInterface>|string $authClass */
            $authClass = config('file-picker.authorization_class', '');

            if ($authClass !== '' && class_exists($authClass) && is_subclass_of($authClass, FilePickerAuthorizationInterface::class)) {
                /** @var FilePickerAuthorizationInterface $instance */
                $instance = app($authClass);

                return $instance;
            }

            return new DefaultAuthorization;
        });

        $this->app->singleton(MediaDriverInterface::class, function (): MediaDriverInterface {
            /** @var string $driverName */
            $driverName = config('file-picker.driver', 'default');

            /** @var MediaTransformerInterface $transformer */
            $transformer = app(MediaTransformerInterface::class);

            return match ($driverName) {
                'default' => new DefaultDriver($transformer),
                'plank' => new PlankMediaDriver($transformer),
                default => $this->resolveCustomDriver($driverName, $transformer),
            };
        });
    }

    private function registerLivewireComponents(): void
    {
        Livewire::component('file-picker', FilePicker::class);
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'file-picker');
    }

    private function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'file-picker');
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
    }

    private function registerMigrations(): void
    {
        /** @var string $driver */
        $driver = config('file-picker.driver', 'default');

        if ($driver === 'default') {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            PruneTrashCommand::class,
            PruneOrphansCommand::class,
            StatsCommand::class,
            InstallCommand::class,

        ]);
    }

    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../../config/file-picker.php' => config_path('file-picker.php'),
        ], 'file-picker-config');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/file-picker'),
        ], 'file-picker-views');

        $this->publishes([
            __DIR__.'/../../resources/css' => public_path('vendor/file-picker'),
            __DIR__.'/../../resources/js' => public_path('vendor/file-picker'),
        ], 'file-picker-assets');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'file-picker-migrations');

        $this->publishes([
            __DIR__.'/../../resources/lang' => lang_path('vendor/file-picker'),
        ], 'file-picker-lang');

        $this->publishes([
            __DIR__.'/../../config/file-picker.php' => config_path('file-picker.php'),
            __DIR__.'/../../resources/views' => resource_path('views/vendor/file-picker'),
            __DIR__.'/../../resources/css' => public_path('vendor/file-picker'),
            __DIR__.'/../../resources/js' => public_path('vendor/file-picker'),
            __DIR__.'/../../resources/lang' => lang_path('vendor/file-picker'),
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'file-picker');
    }

    private function resolveCustomDriver(string $driverName, MediaTransformerInterface $transformer): MediaDriverInterface
    {
        if (! class_exists($driverName)) {
            throw DriverNotFoundException::forDriver($driverName);
        }

        if (! is_subclass_of($driverName, MediaDriverInterface::class)) {
            throw DriverNotFoundException::forDriver($driverName);
        }

        /** @var MediaDriverInterface $driver */
        $driver = new $driverName($transformer);

        return $driver;
    }
}
