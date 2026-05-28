<?php

declare(strict_types=1);

use Anil\LivewireFilePicker\Http\Controllers\MediaDownloadController;
use Illuminate\Support\Facades\Route;

/** @var array<string> $middleware */
$middleware = config('file-picker.route_middleware', ['web']);

Route::middleware($middleware)->group(function (): void {
    Route::get('/vendor/anil/livewire-file-picker/{file}', function (string $file) {
        $basePath = realpath(__DIR__ . '/../resources/');

        if ($basePath === false) {
            abort(404);
        }

        $mapping = [
            '.css' => ['dir' => 'css', 'type' => 'text/css; charset=utf-8'],
            '.js'  => ['dir' => 'js', 'type' => 'application/javascript; charset=utf-8'],
        ];

        $matched = null;

        foreach ($mapping as $ext => $config) {
            if (str_ends_with($file, $ext)) {
                $matched = $config;

                break;
            }
        }

        if ($matched === null) {
            abort(404);
        }

        $filePath = $basePath . '/' . $matched['dir'] . '/' . $file;
        $realPath = realpath($filePath);

        if ($realPath === false || ! str_starts_with($realPath, $basePath) || ! file_exists($realPath)) {
            abort(404);
        }

        return response((string) file_get_contents($realPath), 200, [
            'Content-Type'  => $matched['type'],
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    })->where('file', '[a-zA-Z0-9._-]+\.(css|js)');

    Route::get('/file-picker/download/{id}', [MediaDownloadController::class, 'download'])
        ->whereNumber('id')
        ->name('file-picker.download');

    Route::get('/file-picker/download-zip', [MediaDownloadController::class, 'downloadZip'])
        ->name('file-picker.download-zip');
});
