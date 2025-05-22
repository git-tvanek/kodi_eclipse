<?php

declare(strict_types=1);

// =============================================================================
// ✅ CategoryCollection.php
// =============================================================================

namespace App\Collection;

use App\Entity\Category;

/**
 * Typovaná kolekce pro kategorie
 * 
 * @extends Collection<Category>
 */
class CategoryCollection extends Collection
{
    /**
     * Filtruje pouze root kategorie (bez rodiče)
     */
    public function filterRootCategories(): self
    {
        return $this->filter(function(Category $category): bool {
            return $category->getParent() === null;
        });
    }
    
    /**
     * Filtruje podkategorie konkrétní kategorie
     */
    public function filterByParent(int $parentId): self
    {
        return $this->filter(function(Category $category) use ($parentId): bool {
            $parent = $category->getParent();
            return $parent !== null && $parent->getId() === $parentId;
        });
    }
    
    /**
     * Seřadí podle názvu
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Category $a, Category $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getName(), $b->getName())
                : strcmp($b->getName(), $a->getName());
        });
    }
    
    /**
     * Seřadí podle počtu doplňků
     */
    public function sortByAddonCount(string $direction = 'DESC'): self
    {
        return $this->sort(function(Category $a, Category $b) use ($direction) {
            $countA = $a->getAddons()->count();
            $countB = $b->getAddons()->count();
            
            return $direction === 'DESC' ? $countB <=> $countA : $countA <=> $countB;
        });
    }
    
    /**
     * Filtruje kategorie s minimálním počtem doplňků
     */
    public function filterByMinAddonCount(int $minCount): self
    {
        return $this->filter(function(Category $category) use ($minCount): bool {
            return $category->getAddons()->count() >= $minCount;
        });
    }
    
    /**
     * Filtruje aktivní kategorie (ne smazané)
     */
    public function filterActive(): self
    {
        return $this->filter(function(Category $category): bool {
            return !$category->isDeleted();
        });
    }
    
    /**
     * Vytvoří hierarchickou strukturu kategorií
     */
    public function toHierarchy(): array
    {
        $rootCategories = $this->filterRootCategories();
        $result = [];
        
        foreach ($rootCategories as $rootCategory) {
            $result[] = $this->buildCategoryTree($rootCategory);
        }
        
        return $result;
    }
    
    /**
     * Pomocná metoda pro stavbu stromu kategorií
     */
    private function buildCategoryTree(Category $category): array
    {
        $children = $this->filterByParent($category->getId());
        
        return [
            'category' => $category,
            'addon_count' => $category->getAddons()->count(),
            'children' => $children->map(fn(Category $child) => $this->buildCategoryTree($child))
        ];
    }
    
    /**
     * Získá "breadcrumb" cestu ke kategorii
     */
    public function getBreadcrumbPath(int $categoryId): array
    {
        $category = $this->findFirst(fn(Category $c) => $c->getId() === $categoryId);
        if (!$category) {
            return [];
        }
        
        $path = [];
        $current = $category;
        
        while ($current !== null) {
            array_unshift($path, $current);
            $current = $current->getParent();
        }
        
        return $path;
    }
}