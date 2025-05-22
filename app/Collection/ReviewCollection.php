<?php
namespace App\Collection;

use App\Entity\AddonReview;

/**
 * Typovaná kolekce pro recenze s ověřenými metodami
 * 
 * @extends Collection<AddonReview>
 */
class ReviewCollection extends Collection
{
    /**
     * ✅ Používá existující getter metody z AddonReview entity
     */
    public function filterByMinRating(int $minRating): self
    {
        return $this->filter(function(AddonReview $review) use ($minRating) {
            return $review->getRating() >= $minRating;
        });
    }
    
    /**
     * ✅ Používá existující getter metody z AddonReview entity
     */
    public function filterByMaxRating(int $maxRating): self
    {
        return $this->filter(function(AddonReview $review) use ($maxRating) {
            return $review->getRating() <= $maxRating;
        });
    }
    
    /**
     * ✅ Používá existující getter metody z AddonReview entity
     */
    public function sortByCreatedAt(string $direction = 'DESC'): self
    {
        return $this->sort(function(AddonReview $a, AddonReview $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getCreatedAt() <=> $a->getCreatedAt()
                : $a->getCreatedAt() <=> $b->getCreatedAt();
        });
    }
    
    /**
     * ✅ OPRAVENO: Používá reduce místo problematické iterace
     */
    public function getAverageRating(): float
    {
        if ($this->count() === 0) {
            return 0.0;
        }
        
        $sum = $this->reduce(function(int $carry, AddonReview $review): int {
            return $carry + $review->getRating();
        }, 0);
        
        return round($sum / $this->count(), 2);
    }
    
    /**
     * ✅ OPRAVENO: Používá reduce místo problematické iterace
     */
    public function getRatingDistribution(): array
    {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        
        return $this->reduce(function(array $dist, AddonReview $review): array {
            $rating = $review->getRating();
            if (isset($dist[$rating])) {
                $dist[$rating]++;
            }
            return $dist;
        }, $distribution);
    }
    
    /**
     * ✅ Používá existující metody z AddonReview entity
     */
    public function filterActive(): self
    {
        return $this->filter(function(AddonReview $review): bool {
            return $review->isActive();
        });
    }
    
    /**
     * ✅ Používá existující metody z AddonReview entity
     */
    public function filterVerified(): self
    {
        return $this->filter(function(AddonReview $review): bool {
            return $review->isVerified();
        });
    }
    
    /**
     * ✅ Používá existující metody z AddonReview entity
     */
    public function filterWithComment(): self
    {
        return $this->filter(function(AddonReview $review): bool {
            $comment = $review->getComment();
            return $comment !== null && trim($comment) !== '';
        });
    }
    
    /**
     * ✅ Sentiment analýza používající existující metody
     */
    public function getSentimentAnalysis(): array
    {
        $positive = $this->filter(fn(AddonReview $r) => $r->isPositive())->count();
        $neutral = $this->filter(fn(AddonReview $r) => $r->isNeutral())->count();
        $negative = $this->filter(fn(AddonReview $r) => $r->isNegative())->count();
        $total = $this->count();
        
        return [
            'positive' => $positive,
            'neutral' => $neutral,
            'negative' => $negative,
            'total' => $total,
            'sentiment_score' => $total > 0 ? round(($positive - $negative) / $total, 2) : 0,
            'average_rating' => $this->getAverageRating()
        ];
    }
}