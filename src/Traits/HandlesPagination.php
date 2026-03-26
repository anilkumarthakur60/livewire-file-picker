<?php

declare(strict_types=1);

namespace Anil\LivewireFilePicker\Traits;

/**
 * @property int $currentPage
 * @property int $totalPages
 * @property int $totalItems
 * @property int $perPage
 * @property int $renderTimestamp
 *
 * @method void loadMediaItems()
 */
trait HandlesPagination
{
    public function nextPage(): void
    {
        if ($this->currentPage >= $this->totalPages) {
            return;
        }

        $this->currentPage++;
        $this->renderTimestamp = time();
        $this->loadMediaItems();
    }

    public function previousPage(): void
    {
        if ($this->currentPage <= 1) {
            return;
        }

        $this->currentPage--;
        $this->renderTimestamp = time();
        $this->loadMediaItems();
    }

    public function goToPage(int $page): void
    {
        if ($page < 1 || $page > $this->totalPages) {
            return;
        }

        $this->currentPage = $page;
        $this->renderTimestamp = time();
        $this->loadMediaItems();
    }

    public function resetPagination(): void
    {
        $this->currentPage = 1;
    }

    protected function calculatePagination(int $totalItems): void
    {
        $this->totalItems = $totalItems;
        $this->totalPages = $this->perPage > 0
            ? (int) ceil($totalItems / $this->perPage)
            : 1;

        $this->currentPage = match (true) {
            $this->currentPage > $this->totalPages && $this->totalPages > 0 => $this->totalPages,
            $this->currentPage < 1 => 1,
            default => $this->currentPage,
        };
    }

    protected function getPaginationOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * @return array<int|string>
     */
    public function getVisiblePagesProperty(): array
    {
        if ($this->totalPages <= 0) {
            return [];
        }

        if ($this->totalPages <= 7) {
            return range(1, $this->totalPages);
        }

        /** @var array<int|string> $pages */
        $pages = [];
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);

        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = $this->totalPages;
        }

        return $pages;
    }

    public function getPaginationInfoProperty(): string
    {
        if ($this->totalItems === 0) {
            return 'No items found';
        }

        $start = $this->getPaginationOffset() + 1;
        $end = min($start + $this->perPage - 1, $this->totalItems);

        return "Showing {$start} to {$end} of {$this->totalItems} items";
    }
}
