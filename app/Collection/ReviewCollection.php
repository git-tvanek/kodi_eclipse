<?php

declare(strict_types=1);

namespace App\Collection;

use App\Entity\AddonReview;

/**
 * Typovaná kolekce pro recenze
 * 
 * @extends Collection<AddonReview>
 */
class ReviewCollection extends Collection
{
    /**
     * Vrátí průměrné hodnocení všech recenzí v kolekci
     * 
     * @return float
     */
    public function getAverageRating(): float
    {
        if ($this->count() === 0) {
            return 0.0;
        }
        
        // OPRAVA: Použití správného přístupu k položkám kolekce
        $sum = array_reduce($this->toArray(), function($carry, AddonReview $review) {
            return $carry + $review->getRating(); // Používání getter metody
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
            1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0
        ];
        
        // OPRAVA: Správný způsob iterace přes kolekci
        foreach ($this as $review) {
            $rating = $review->getRating();
            if (isset($distribution[$rating])) {
                $distribution[$rating]++;
            }
        }
        
        return $distribution;
    }
}