<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\AddonReview;

/**
 * Typovaná kolekce pro recenze
 * 
 * @extends Collection<AddonReview>
 */
class ReviewCollection extends Collection
{
    /**
     * Filtruje recenze podle minimálního hodnocení
     * 
     * @param int $minRating
     * @return self
     */
    public function filterByMinRating(int $minRating): self
    {
        return $this->filter(function(AddonReview $review) use ($minRating) {
            return $review->rating >= $minRating;
        });
    }
    
    /**
     * Filtruje recenze podle maximálního hodnocení
     * 
     * @param int $maxRating
     * @return self
     */
    public function filterByMaxRating(int $maxRating): self
    {
        return $this->filter(function(AddonReview $review) use ($maxRating) {
            return $review->rating <= $maxRating;
        });
    }
    
    /**
     * Seřadí recenze podle data vytvoření
     * 
     * @param string $direction
     * @return self
     */
    public function sortByCreatedAt(string $direction = 'DESC'): self
    {
        return $this->sort(function(AddonReview $a, AddonReview $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->created_at <=> $a->created_at
                : $a->created_at <=> $b->created_at;
        });
    }
    
    /**
     * Vrátí průměrné hodnocení všech recenzí v kolekci
     * 
     * @return float
     */
    public function getAverageRating(): float
    {
        if ($this->count() === 0) {
            return 0;
        }
        
        $sum = array_reduce($this->items, function($carry, AddonReview $review) {
            return $carry + $review->rating;
        }, 0);
        
        return round($sum / $this->count(), 2);
    }
    
    /**
     * Vrátí distribuci hodnocení (počet recenzí pro každé hodnocení 1-5)
     * 
     * @return array<int, int>
     */
    public function getRatingDistribution(): array
    {
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];
        
        foreach ($this->items as $review) {
            if (isset($distribution[$review->rating])) {
                $distribution[$review->rating]++;
            }
        }
        
        return $distribution;
    }
}