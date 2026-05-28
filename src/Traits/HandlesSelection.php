<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Traits;

/**
 * @property array<int> $selected
 * @property bool $multiple
 * @property int $maxFiles
 * @property int $renderTimestamp
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
trait HandlesSelection
{
    public function isItemSelected(int $mediaId): bool
    {
        $this->normalizeSelected();

        return in_array($mediaId, $this->selected, true);
    }

    public function getCheckboxState(int $mediaId): string
    {
        return $this->isItemSelected($mediaId) ? 'checked' : '';
    }

    public function getSelectedCountProperty(): int
    {
        $this->normalizeSelected();

        return count($this->selected);
    }

    public function isMaxSelectionReached(): bool
    {
        return count($this->selected) >= $this->maxFiles;
    }

    public function getRemainingSelectionsProperty(): int
    {
        return max(0, $this->maxFiles - count($this->selected));
    }

    protected function normalizeSelected(): void
    {
        $this->selected = array_values(array_map(intval(...), array_filter($this->selected)));
    }

    protected function toggleMultipleSelection(int $mediaId): void
    {
        if ($this->isItemSelected($mediaId)) {
            $this->selected = array_values(array_diff($this->selected, [$mediaId]));

            return;
        }

        if (count($this->selected) < $this->maxFiles) {
            $this->selected[] = $mediaId;
        }
    }

    protected function toggleSingleSelection(int $mediaId): void
    {
        $this->selected = $this->isItemSelected($mediaId) ? [] : [$mediaId];
    }
}
