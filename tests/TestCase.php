<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Tests;

use Anil\LivewireFilePicker\Models\FilePickerMedia;
use Anil\LivewireFilePicker\Providers\FilePickerProvider;
use Illuminate\Foundation\Application;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Plank\Mediable\MediableServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../vendor/plank/laravel-mediable/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MediableServiceProvider::class,
            FilePickerProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('file-picker.driver', 'plank');
        $app['config']->set('file-picker.drivers.plank.disk', 'public');
        $app['config']->set('file-picker.drivers.plank.model', FilePickerMedia::class);

        $app['config']->set('mediable.model', FilePickerMedia::class);
        $app['config']->set('mediable.allowed_disks', ['public']);
    }
}
