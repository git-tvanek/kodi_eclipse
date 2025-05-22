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

    /**
     * 🔥 Nejpopulárnější kategorie podle stažení
     */
    public function getMostPopular(int $limit = 10): self
    {
        return $this->sort(function(Category $a, Category $b) {
                $downloadsA = 0;
                $downloadsB = 0;
                
                foreach ($a->getAddons() as $addon) {
                    $downloadsA += $addon->getDownloadsCount();
                }
                
                foreach ($b->getAddons() as $addon) {
                    $downloadsB += $addon->getDownloadsCount();
                }
                
                return $downloadsB <=> $downloadsA;
            })
            ->take($limit);
    }

    /**
     * ⭐ Kategorie s nejlepším průměrným hodnocením
     */
    public function getBestRated(int $minAddons = 5, int $limit = 10): self
    {
        return $this->filterByMinAddonCount($minAddons)
            ->sort(function(Category $a, Category $b) {
                $avgA = $this->calculateAverageRating($a);
                $avgB = $this->calculateAverageRating($b);
                return $avgB <=> $avgA;
            })
            ->take($limit);
    }

    /**
     * 🔥 Aktivní kategorie
     */
    public function getActiveCategories(int $days = 30): self
    {
        $since = new \DateTime("-{$days} days");
        
        return $this->filter(function(Category $category) use ($since) {
            foreach ($category->getAddons() as $addon) {
                if ($addon->getCreatedAt() >= $since) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * 📈 Seřadit podle růstu
     */
    public function sortByGrowth(string $direction = 'DESC'): self
    {
        $lastMonth = new \DateTime('-1 month');
        
        return $this->sort(function(Category $a, Category $b) use ($direction, $lastMonth) {
            $newAddonsA = 0;
            $newAddonsB = 0;
            
            foreach ($a->getAddons() as $addon) {
                if ($addon->getCreatedAt() >= $lastMonth) $newAddonsA++;
            }
            
            foreach ($b->getAddons() as $addon) {
                if ($addon->getCreatedAt() >= $lastMonth) $newAddonsB++;
            }
            
            return $direction === 'DESC' ? $newAddonsB <=> $newAddonsA : $newAddonsA <=> $newAddonsB;
        });
    }

    /**
     * 📊 Kategorie se statistikami
     */
    public function withStats(): array
    {
        return $this->map(function(Category $category) {
            return [
                'category' => $category,
                'stats' => [
                    'addon_count' => $category->getAddons()->count(),
                    'total_downloads' => $this->calculateTotalDownloads($category),
                    'average_rating' => $this->calculateAverageRating($category),
                    'growth_last_month' => $this->calculateGrowth($category, 30),
                    'newest_addon' => $this->getNewestAddon($category)
                ]
            ];
        });
    }

    // ========== POMOCNÉ METODY ==========

    private function calculateTotalDownloads(Category $category): int
    {
        $total = 0;
        foreach ($category->getAddons() as $addon) {
            $total += $addon->getDownloadsCount();
        }
        return $total;
    }

    private function calculateAverageRating(Category $category): float
    {
        $addons = $category->getAddons();
        if ($addons->count() === 0) return 0;
        
        $totalRating = 0;
        foreach ($addons as $addon) {
            $totalRating += $addon->getRating();
        }
        
        return round($totalRating / $addons->count(), 2);
    }

    private function calculateGrowth(Category $category, int $days): int
    {
        $since = new \DateTime("-{$days} days");
        $count = 0;
        
        foreach ($category->getAddons() as $addon) {
            if ($addon->getCreatedAt() >= $since) {
                $count++;
            }
        }
        
        return $count;
    }

    private function getNewestAddon(Category $category)
    {
        $newest = null;
        foreach ($category->getAddons() as $addon) {
            if ($newest === null || $addon->getCreatedAt() > $newest->getCreatedAt()) {
                $newest = $addon;
            }
        }
        return $newest;
    }
}