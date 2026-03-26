<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Traits;

use Anil\LivewireFilePicker\Contracts\FilePickerAuthorizationInterface;
use Anil\LivewireFilePicker\Contracts\MediaDriverInterface;
use Anil\LivewireFilePicker\Enums\FileType;
use Anil\LivewireFilePicker\Exceptions\UploadFailedException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * @property array<int, TemporaryUploadedFile> $uploadedFiles
 * @property bool $isUploading
 * @property string $uploadMessage
 * @property string $uploadStatus
 * @property string $currentTab
 * @property array<string> $allowedTypes
 *
 * @method void loadMediaItems()
 * @method MediaDriverInterface driver()
 * @method FilePickerAuthorizationInterface authorization()
 * @method array<string, mixed> validate(array<string, mixed> $rules = [], array<string, string> $messages = [], array<string, string> $attributes = [])
 * @method void dispatch(string $event, mixed ...$params)
 */
trait HandlesFileUpload
{
    use WithFileUploads;

    public function uploadFiles(): void
    {
        if ($this->uploadedFiles === []) {
            return;
        }

        if (! $this->authorization()->canUpload()) {
            $this->uploadMessage = 'You are not authorized to upload files.';
            $this->uploadStatus = 'error';

            return;
        }

        $this->validateUploadedFiles();
        $this->processUpload();
    }

    public function removePendingFile(int $index): void
    {
        if (! isset($this->uploadedFiles[$index])) {
            return;
        }

        $file = $this->uploadedFiles[$index];

        try {
            $file->delete();
        } catch (\Throwable) {
            // Ignore cleanup errors
        }

        unset($this->uploadedFiles[$index]);
        $this->uploadedFiles = array_values($this->uploadedFiles);
    }

    public function getAcceptAttribute(): string
    {
        $mimeTypes = $this->getAllowedMimeTypesForValidation();

        return $mimeTypes === [] ? '*/*' : implode(',', $mimeTypes);
    }

    protected function validateUploadedFiles(): void
    {
        /** @var int $maxSize */
        $maxSize = config('file-picker.max_file_size', 102400);
        $allowedMimes = $this->getAllowedMimeTypesForValidation();

        /** @var array<string, array<int, string>> $rules */
        $rules = [
            'uploadedFiles.*' => [
                'file',
                'max:'.$maxSize,
            ],
        ];

        if ($allowedMimes !== []) {
            $rules['uploadedFiles.*'][] = 'mimes:'.implode(',', $this->getAllowedExtensionsForValidation());
        }

        $this->validate($rules, [
            'uploadedFiles.*.max' => 'The file must not be larger than '.((int) $maxSize / 1024).' MB.',
            'uploadedFiles.*.mimes' => 'The file type is not allowed.',
        ]);
    }

    protected function processUpload(): void
    {
        $this->isUploading = true;
        $this->uploadMessage = 'Uploading files...';

        $uploadedCount = 0;
        $failedCount = 0;
        /** @var array<string> $failedFiles */
        $failedFiles = [];

        try {
            $driver = $this->driver();

            foreach ($this->uploadedFiles as $file) {
                try {
                    $driver->upload($file);
                    $uploadedCount++;
                } catch (UploadFailedException $e) {
                    $failedCount++;
                    $failedFiles[] = $e->getMessage();
                } catch (\Throwable $e) {
                    $failedCount++;
                    $failedFiles[] = $file->getClientOriginalName().': '.$e->getMessage();
                }
            }

            $this->setUploadResultMessage($uploadedCount, $failedCount, $failedFiles);
            $this->loadMediaItems();

            if ($uploadedCount > 0) {
                $this->currentTab = 'library';
            }
        } finally {
            $this->isUploading = false;
            $this->cleanupTemporaryFiles();
            $this->uploadedFiles = [];
            $this->dispatch('clearUploadMessage');
        }
    }

    protected function cleanupTemporaryFiles(): void
    {
        foreach ($this->uploadedFiles as $file) {
            try {
                $file->delete();

                $jsonFilePath = $file->getRealPath().'.json';
                if (file_exists($jsonFilePath)) {
                    @unlink($jsonFilePath);
                }
            } catch (\Throwable) {
                // Ignore cleanup errors
            }
        }
    }

    /**
     * @param  array<string>  $failedFiles
     */
    protected function setUploadResultMessage(int $uploaded, int $failed, array $failedFiles): void
    {
        if ($failed === 0) {
            $this->uploadMessage = $uploaded === 1
                ? 'File uploaded successfully!'
                : "{$uploaded} files uploaded successfully!";
            $this->uploadStatus = 'success';

            return;
        }

        if ($uploaded === 0) {
            $this->uploadMessage = 'Upload failed: '.implode(', ', $failedFiles);
            $this->uploadStatus = 'error';

            return;
        }

        $this->uploadMessage = "{$uploaded} files uploaded, {$failed} failed.";
        $this->uploadStatus = 'warning';
    }

    /**
     * @return array<string>
     */
    protected function getAllowedExtensionsForValidation(): array
    {
        if ($this->allowedTypes === [] || in_array('all', $this->allowedTypes, true)) {
            return [];
        }

        /** @var array<string> $extensions */
        $extensions = [];

        foreach ($this->allowedTypes as $type) {
            $fileType = FileType::tryFrom($type);

            if ($fileType !== null) {
                $extensions = [...$extensions, ...$fileType->extensions()];
            }
        }

        return array_values(array_unique($extensions));
    }

    /**
     * @return array<string>
     */
    protected function getAllowedMimeTypesForValidation(): array
    {
        if ($this->allowedTypes === [] || in_array('all', $this->allowedTypes, true)) {
            return [];
        }

        /** @var array<string> $mimeTypes */
        $mimeTypes = [];

        foreach ($this->allowedTypes as $type) {
            $fileType = FileType::tryFrom($type);

            if ($fileType !== null) {
                $mimeTypes = [...$mimeTypes, ...$fileType->mimeTypes()];
            }
        }

        return array_values(array_unique($mimeTypes));
    }

    protected function resetUploadState(): void
    {
        $this->cleanupTemporaryFiles();
        $this->uploadedFiles = [];
        $this->uploadMessage = '';
        $this->uploadStatus = '';
        $this->isUploading = false;
    }
}
