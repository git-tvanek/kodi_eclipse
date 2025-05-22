<?php

declare(strict_types=1);

namespace App\Collection;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Stránkovaná kolekce - nyní s Doctrine podporou
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
     * @param Collection<T>|DoctrineCollection<int, T>|iterable<T> $items
     * @param int $totalCount
     * @param int $page
     * @param int $itemsPerPage
     * @param int $pages
     */
    public function __construct(
        $items, 
        int $totalCount, 
        int $page, 
        int $itemsPerPage, 
        int $pages
    ) {
        // Automaticky wrap do naší Collection třídy pokud není
        $this->items = $items instanceof Collection ? $items : new Collection($items);
        $this->totalCount = $totalCount;
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
        $this->pages = $pages;
    }
    
    /**
     * Factory metoda pro vytvoření z Doctrine Collection
     * 
     * @template U
     * @param DoctrineCollection<int, U> $doctrineCollection
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<U>
     */
    public static function fromDoctrineCollection(
        DoctrineCollection $doctrineCollection,
        int $page = 1,
        int $itemsPerPage = 10
    ): self {
        $totalCount = $doctrineCollection->count();
        $pages = (int) ceil($totalCount / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        // Použijeme Doctrine slice pro efektivní stránkování
        $items = $doctrineCollection->slice($offset, $itemsPerPage);
        
        return new self(
            new Collection($items),
            $totalCount,
            $page,
            $itemsPerPage,
            $pages
        );
    }
    
    /**
     * Factory metoda s Doctrine Criteria
     * 
     * @template U
     * @param DoctrineCollection<int, U> $doctrineCollection
     * @param Criteria $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<U>
     */
    public static function fromDoctrineCollectionWithCriteria(
        DoctrineCollection $doctrineCollection,
        Criteria $criteria,
        int $page = 1,
        int $itemsPerPage = 10
    ): self {
        // Nejdříve aplikujeme criteria
        $filteredCollection = $doctrineCollection->matching($criteria);
        
        // Pak stránkujeme
        return self::fromDoctrineCollection($filteredCollection, $page, $itemsPerPage);
    }
    
    // ===== ZACHOVANÉ PŮVODNÍ METODY =====
    
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
     * ZACHOVANÁ metoda - převede kolekci na standardní formát pole pro API nebo šablony
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
    
    // ===== NOVÉ METODY PRO DOCTRINE INTEGRACI =====
    
    /**
     * Aplikuje filtry na current items
     * @param callable $predicate
     * @return self
     */
    public function filterItems(callable $predicate): self
    {
        $filteredItems = $this->items->filter($predicate);
        
        return new self(
            $filteredItems,
            $filteredItems->count(), // totalCount se změní
            1, // resetujeme na první stránku
            $this->itemsPerPage,
            (int) ceil($filteredItems->count() / $this->itemsPerPage)
        );
    }
    
    /**
     * Aplikuje Doctrine Criteria na current items
     * @param Criteria $criteria
     * @return self
     */
    public function applyCriteria(Criteria $criteria): self
    {
        $filteredItems = $this->items->matching($criteria);
        
        return new self(
            $filteredItems,
            $filteredItems->count(),
            1,
            $this->itemsPerPage,
            (int) ceil($filteredItems->count() / $this->itemsPerPage)
        );
    }
    
    /**
     * Sortuje items pomocí Doctrine Criteria
     * @param string $field
     * @param string $direction
     * @return self
     */
    public function sortBy(string $field, string $direction = 'ASC'): self
    {
        $sortedItems = $this->items->sortBy($field, $direction);
        
        return new self(
            $sortedItems,
            $this->totalCount,
            $this->page,
            $this->itemsPerPage,
            $this->pages
        );
    }
    
    /**
     * Převede na JSON pro API
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
    
    /**
     * Získá metadata o stránkování
     */
    public function getPaginationMetadata(): array
    {
        return [
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'totalCount' => $this->totalCount,
            'pages' => $this->pages,
            'hasNextPage' => $this->hasNextPage(),
            'hasPreviousPage' => $this->hasPreviousPage(),
            'nextPage' => $this->getNextPage(),
            'previousPage' => $this->getPreviousPage(),
            'offset' => ($this->page - 1) * $this->itemsPerPage,
            'limit' => $this->itemsPerPage
        ];
    }
    
    /**
     * Pro debugging - informace o kolekci
     */
    public function getDebugInfo(): array
    {
        return [
            'itemsType' => get_class($this->items),
            'itemCount' => $this->items->count(),
            'totalCount' => $this->totalCount,
            'pagination' => $this->getPaginationMetadata()
        ];
    }
}