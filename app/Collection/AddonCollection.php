<?php

declare(strict_types=1);

// =============================================================================
// ✅ KOMPLETNĚ OPRAVENÁ AddonCollection.php
// =============================================================================

namespace App\Collection;

use App\Entity\Addon;

/**
 * Typovaná kolekce pro doplňky s ověřenými metodami
 * 
 * @extends Collection<Addon>
 */
class AddonCollection extends Collection
{
    /**
     * ✅ Používá existující getter metody z Addon entity
     */
    public function sortByDownloads(string $direction = 'DESC'): self
    {
        return $this->sort(function(Addon $a, Addon $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getDownloadsCount() <=> $a->getDownloadsCount()
                : $a->getDownloadsCount() <=> $b->getDownloadsCount();
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Addon entity
     */
    public function sortByRating(string $direction = 'DESC'): self
    {
        return $this->sort(function(Addon $a, Addon $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getRating() <=> $a->getRating()
                : $a->getRating() <=> $b->getRating();
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Addon entity
     */
    public function filterByCategory(int $categoryId): self
    {
        return $this->filter(function(Addon $addon) use ($categoryId) {
            return $addon->getCategory()->getId() === $categoryId;
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Addon entity
     */
    public function filterByMinRating(float $minRating): self
    {
        return $this->filter(function(Addon $addon) use ($minRating) {
            return $addon->getRating() >= $minRating;
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Addon entity
     */
    public function filterByKodiVersion(string $version): self
    {
        return $this->filter(function(Addon $addon) use ($version) {
            $minVersion = $addon->getKodiVersionMin();
            $maxVersion = $addon->getKodiVersionMax();
            
            if ($minVersion && version_compare($version, $minVersion, '<')) {
                return false;
            }
            
            if ($maxVersion && version_compare($version, $maxVersion, '>')) {
                return false;
            }
            
            return true;
        });
    }
    
    // ✅ Dodatečné užitečné metody
    public function getTotalDownloads(): int
    {
        return $this->reduce(function(int $total, Addon $addon): int {
            return $total + $addon->getDownloadsCount();
        }, 0);
    }
    
    public function getAverageRating(): float
    {
        if ($this->isEmpty()) {
            return 0.0;
        }
        
        $totalRating = $this->reduce(function(float $total, Addon $addon): float {
            return $total + $addon->getRating();
        }, 0.0);
        
        return round($totalRating / $this->count(), 2);
    }
}