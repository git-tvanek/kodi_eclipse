<?php

declare(strict_types=1);

namespace App\Collection;

/**
 * Stránkovaná kolekce
 * 
 * @template T
 */
class PaginatedCollection
{
    /** @var Collection<T> */
    private Collection $items;
    
    /** @var int */
    private int $totalCount;
    
    /** @var int */
    private int $page;
    
    /** @var int */
    private int $itemsPerPage;
    
    /** @var int */
    private int $pages;
    
    /**
     * @param Collection<T> $items
     * @param int $totalCount
     * @param int $page
     * @param int $itemsPerPage
     * @param int $pages
     */
    public function __construct(
        Collection $items, 
        int $totalCount, 
        int $page, 
        int $itemsPerPage, 
        int $pages
    ) {
        $this->items = $items;
        $this->totalCount = $totalCount;
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
        $this->pages = $pages;
    }
    
    /**
     * @return Collection<T>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
    
    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
    
    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }
    
    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    
    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->pages;
    }
    
    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->page < $this->pages;
    }
    
    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
    
    /**
     * @return int|null
     */
    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->page + 1 : null;
    }
    
    /**
     * @return int|null
     */
    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->page - 1 : null;
    }
    
    /**
     * Převede kolekci na standardní formát pole pro API nebo šablony
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items->toArray(),
            'totalCount' => $this->totalCount,
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'pages' => $this->pages
        ];
    }
}