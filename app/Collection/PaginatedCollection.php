<?php

declare(strict_types=1);

namespace App\Collection;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Closure;

/**
 * Unifikovaná stránkovaná kolekce s PHP 8+ features a factory methods
 * 
 * @template T
 */
class PaginatedCollection
{
    /** @var Collection<T> */
    private Collection $items;
    
    private readonly int $totalCount;
    private readonly int $page;
    private readonly int $itemsPerPage;
    private readonly int $pages;
    
    /**
     * ✅ CONSTRUCTOR s union types a readonly properties
     * 
     * @param Collection<T>|DoctrineCollection<int, T>|iterable<T> $items
     */
    public function __construct(
        Collection|DoctrineCollection|iterable $items, 
        int $totalCount, 
        int $page, 
        int $itemsPerPage, 
        int $pages
    ) {
        // ✅ Smart conversion podle typu
        $this->items = match (true) {
            $items instanceof Collection => $items,
            $items instanceof DoctrineCollection => Collection::fromDoctrineCollection($items),
            default => Collection::fromArray(is_array($items) ? $items : iterator_to_array($items))
        };
        
        $this->totalCount = $totalCount;
        $this->page = max(1, $page);
        $this->itemsPerPage = max(1, $itemsPerPage);
        $this->pages = max(0, $pages);
    }
    
    // =========================================================================
    // ✅ FACTORY METHODS pro různé use cases
    // =========================================================================
    
    /**
     * Vytvoří z Doctrine Collection s automatickým stránkováním
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
            items: Collection::fromArray($items),
            totalCount: $totalCount,
            page: $page,
            itemsPerPage: $itemsPerPage,
            pages: $pages
        );
    }
    
    /**
     * Vytvoří z pole s manuálním nastavením
     * 
     * @template U
     * @param array<U> $items
     * @param int $totalCount
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<U>
     */
    public static function fromArray(
        array $items,
        int $totalCount,
        int $page = 1,
        int $itemsPerPage = 10
    ): self {
        $pages = (int) ceil($totalCount / $itemsPerPage);
        
        return new self(
            items: Collection::fromArray($items),
            totalCount: $totalCount,
            page: $page,
            itemsPerPage: $itemsPerPage,
            pages: $pages
        );
    }
    
    /**
     * Vytvoří z Custom Collection
     * 
     * @template U
     * @param Collection<U> $collection
     * @param int $totalCount
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<U>
     */
    public static function fromCollection(
        Collection $collection,
        int $totalCount,
        int $page = 1,
        int $itemsPerPage = 10
    ): self {
        $pages = (int) ceil($totalCount / $itemsPerPage);
        
        return new self(
            items: $collection,
            totalCount: $totalCount,
            page: $page,
            itemsPerPage: $itemsPerPage,
            pages: $pages
        );
    }
    
    /**
     * Factory s Doctrine Criteria
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
        // ✅ Kontrola zda collection podporuje matching
        if (!$doctrineCollection instanceof Selectable) {
            throw new \InvalidArgumentException('DoctrineCollection must implement Selectable interface for criteria matching');
        }
        
        // Nejdříve aplikujeme criteria
        $filteredCollection = $doctrineCollection->matching($criteria);
        
        // Pak stránkujeme
        return self::fromDoctrineCollection($filteredCollection, $page, $itemsPerPage);
    }
    
    /**
     * ✅ Factory pro prázdnou kolekci
     * 
     * @template U
     * @return PaginatedCollection<U>
     */
    public static function empty(): self
    {
        return new self(
            items: Collection::empty(),
            totalCount: 0,
            page: 1,
            itemsPerPage: 10,
            pages: 0
        );
    }
    
    // =========================================================================
    // ✅ GETTERS (readonly properties)
    // =========================================================================
    
    /**
     * @return Collection<T>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
    
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
    
    public function getPage(): int
    {
        return $this->page;
    }
    
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
    
    public function getPages(): int
    {
        return $this->pages;
    }
    
    // =========================================================================
    // ✅ NAVIGATION METHODS
    // =========================================================================
    
    public function hasNextPage(): bool
    {
        return $this->page < $this->pages;
    }
    
    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }
    
    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->page + 1 : null;
    }
    
    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->page - 1 : null;
    }
    
    public function getFirstPage(): int
    {
        return 1;
    }
    
    public function getLastPage(): int
    {
        return max(1, $this->pages);
    }
    
    /**
     * ✅ Získá čísla stránek pro pagination UI
     * 
     * @param int $range Počet stránek kolem aktuální stránky
     * @return array<int>
     */
    public function getPageRange(int $range = 5): array
    {
        $start = max(1, $this->page - floor($range / 2));
        $end = min($this->pages, $start + $range - 1);
        
        // Adjust start if we're near the end
        if ($end - $start + 1 < $range) {
            $start = max(1, $end - $range + 1);
        }
        
        return range((int)$start, (int)$end);
    }
    
    // =========================================================================
    // ✅ INFORMATION METHODS
    // =========================================================================
    
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
    
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }
    
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->itemsPerPage;
    }
    
    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }
    
    /**
     * ✅ Získá číslo prvního itemu na stránce
     */
    public function getFirstItemNumber(): int
    {
        return $this->isEmpty() ? 0 : $this->getOffset() + 1;
    }
    
    /**
     * ✅ Získá číslo posledního itemu na stránce
     */
    public function getLastItemNumber(): int
    {
        return $this->isEmpty() 
            ? 0 
            : min($this->totalCount, $this->getOffset() + $this->items->count());
    }
    
    // =========================================================================
    // ✅ TRANSFORMATION METHODS
    // =========================================================================
    
    /**
     * Převede na standardní pole pro API nebo šablony
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items->toArray(),
            'totalCount' => $this->totalCount,
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'pages' => $this->pages,
            'hasNextPage' => $this->hasNextPage(),
            'hasPreviousPage' => $this->hasPreviousPage(),
            'nextPage' => $this->getNextPage(),
            'previousPage' => $this->getPreviousPage()
        ];
    }
    
    /**
     * ✅ Převede na pole rekurzivně
     */
    public function toArrayRecursive(): array
    {
        return [
            'items' => $this->items->toArrayRecursive(),
            'totalCount' => $this->totalCount,
            'page' => $this->page,
            'itemsPerPage' => $this->itemsPerPage,
            'pages' => $this->pages,
            'hasNextPage' => $this->hasNextPage(),
            'hasPreviousPage' => $this->hasPreviousPage(),
            'nextPage' => $this->getNextPage(),
            'previousPage' => $this->getPreviousPage(),
            'firstItemNumber' => $this->getFirstItemNumber(),
            'lastItemNumber' => $this->getLastItemNumber(),
            'offset' => $this->getOffset(),
            'limit' => $this->getLimit()
        ];
    }
    
    /**
     * Převede na JSON pro API
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->toArrayRecursive(), $flags | JSON_THROW_ON_ERROR, $depth);
    }
    
    // =========================================================================
    // ✅ FUNCTIONAL METHODS (immutable operations)
    // =========================================================================
    
    /**
     * Aplikuje filtry na current items (vytvoří novou instanci)
     * 
     * @param Closure(T): bool $predicate
     * @return self<T>
     */
    public function filterItems(Closure $predicate): self
    {
        $filteredItems = $this->items->filter($predicate);
        
        return new self(
            items: $filteredItems,
            totalCount: $filteredItems->count(), // totalCount se změní
            page: 1, // resetujeme na první stránku
            itemsPerPage: $this->itemsPerPage,
            pages: (int) ceil($filteredItems->count() / $this->itemsPerPage)
        );
    }
    
    /**
     * Aplikuje Doctrine Criteria na current items
     * 
     * @param Criteria $criteria
     * @return self<T>
     */
    public function applyCriteria(Criteria $criteria): self
    {
        $filteredItems = $this->items->matching($criteria);
        
        return new self(
            items: $filteredItems,
            totalCount: $filteredItems->count(),
            page: 1,
            itemsPerPage: $this->itemsPerPage,
            pages: (int) ceil($filteredItems->count() / $this->itemsPerPage)
        );
    }
    
    /**
     * Sortuje items pomocí field názvu
     * 
     * @param string $field
     * @param string $direction
     * @return self<T>
     */
    public function sortBy(string $field, string $direction = 'ASC'): self
    {
        $sortedItems = $this->items->sortBy($field, $direction);
        
        return new self(
            items: $sortedItems,
            totalCount: $this->totalCount,
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
            pages: $this->pages
        );
    }
    
    /**
     * ✅ Map items to different type
     * 
     * @template U
     * @param Closure(T): U $mapper
     * @return PaginatedCollection<U>
     */
    public function mapItems(Closure $mapper): PaginatedCollection
    {
        $mappedArray = $this->items->map($mapper);
        
        return new PaginatedCollection(
            items: Collection::fromArray($mappedArray),
            totalCount: $this->totalCount,
            page: $this->page,
            itemsPerPage: $this->itemsPerPage,
            pages: $this->pages
        );
    }
    
    // =========================================================================
    // ✅ METADATA METHODS
    // =========================================================================
    
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
            'firstPage' => $this->getFirstPage(),
            'lastPage' => $this->getLastPage(),
            'offset' => $this->getOffset(),
            'limit' => $this->getLimit(),
            'firstItemNumber' => $this->getFirstItemNumber(),
            'lastItemNumber' => $this->getLastItemNumber(),
            'pageRange' => $this->getPageRange()
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
            'isEmpty' => $this->isEmpty(),
            'pagination' => $this->getPaginationMetadata(),
            'underlyingCollectionType' => get_class($this->items->getDoctrineCollection())
        ];
    }
    
    // =========================================================================
    // ✅ CONVENIENCE METHODS
    // =========================================================================
    
    /**
     * Quick access to first item
     * 
     * @return T|null
     */
    public function firstItem()
    {
        return $this->items->first();
    }
    
    /**
     * Quick access to last item
     * 
     * @return T|null
     */
    public function lastItem()
    {
        return $this->items->last();
    }
    
    /**
     * Get item count on current page
     */
    public function getItemCount(): int
    {
        return $this->items->count();
    }
    
    /**
     * ✅ Check if this is the first page
     */
    public function isFirstPage(): bool
    {
        return $this->page === 1;
    }
    
    /**
     * ✅ Check if this is the last page
     */
    public function isLastPage(): bool
    {
        return $this->page === $this->pages || $this->pages === 0;
    }
    
    /**
     * ✅ Get percentage of completion
     */
    public function getCompletionPercentage(): float
    {
        if ($this->pages === 0) {
            return 100.0;
        }
        
        return round(($this->page / $this->pages) * 100, 2);
    }
}