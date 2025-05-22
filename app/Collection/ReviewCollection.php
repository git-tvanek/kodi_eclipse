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

     /**
     * ⭐ Recenze s konkrétním hodnocením
     */
    public function withRating(int $rating): self
    {
        return $this->filter(function(AddonReview $review) use ($rating) {
            return $review->getRating() === $rating;
        });
    }

    /**
     * 😊 Pozitivní recenze (4-5 hvězd)
     */
    public function getPositive(): self
    {
        return $this->filterByMinRating(4);
    }

    /**
     * 😞 Negativní recenze (1-2 hvězdy)
     */
    public function getNegative(): self
    {
        return $this->filterByMaxRating(2);
    }

    /**
     * 😐 Neutrální recenze (3 hvězdy)
     */
    public function getNeutral(): self
    {
        return $this->withRating(3);
    }

    /**
     * 🆕 Nejnovější recenze
     */
    public function getRecent(int $days = 7): self
    {
        $since = new \DateTime("-{$days} days");
        return $this->filter(function(AddonReview $review) use ($since) {
            return $review->getCreatedAt() >= $since;
        })->sortByCreatedAt('DESC');
    }

    /**
     * 💡 Nejužitečnější recenze
     */
    public function getMostHelpful(int $limit = 10): self
    {
        return $this->filterWithComment()
                   ->filterByMinRating(4)
                   ->sort(function(AddonReview $a, AddonReview $b) {
                       $scoreA = strlen($a->getComment() ?? '') + ($a->getRating() * 10);
                       $scoreB = strlen($b->getComment() ?? '') + ($b->getRating() * 10);
                       return $scoreB <=> $scoreA;
                   })
                   ->take($limit);
    }

    /**
     * 🔍 Vyhledávání v komentářích
     */
    public function searchInComments(string $query): self
    {
        if (empty(trim($query))) {
            return $this;
        }
        
        return $this->filter(function(AddonReview $review) use ($query) {
            $comment = strtolower($review->getComment() ?? '');
            return str_contains($comment, strtolower(trim($query)));
        });
    }

    /**
     * 📈 Trend hodnocení v čase
     */
    public function getRatingTrend(int $months = 12): array
    {
        $trends = [];
        $now = new \DateTime();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = clone $now;
            $date->modify("-{$i} months");
            $monthKey = $date->format('Y-m');
            
            $monthReviews = $this->filter(function(AddonReview $review) use ($date) {
                return $review->getCreatedAt()->format('Y-m') === $date->format('Y-m');
            });
            
            $trends[] = [
                'month' => $monthKey,
                'count' => $monthReviews->count(),
                'average_rating' => $monthReviews->isEmpty() ? 0 : $monthReviews->getAverageRating(),
                'positive_ratio' => $monthReviews->isEmpty() ? 0 : 
                    $monthReviews->getPositive()->count() / $monthReviews->count()
            ];
        }
        
        return $trends;
    }

    /**
     * 🏆 Top reviewers
     */
    public function getTopReviewers(int $limit = 10): array
    {
        $reviewerStats = [];
        
        foreach ($this as $review) {
            $userId = $review->getUser() ? $review->getUser()->getId() : null;
            $userName = $review->getUser() ? $review->getUser()->getUsername() : ($review->getName() ?? 'Anonymous');
            $key = $userId ?? $userName;
            
            if (!isset($reviewerStats[$key])) {
                $reviewerStats[$key] = [
                    'user' => $review->getUser(),
                    'name' => $userName,
                    'review_count' => 0,
                    'total_rating' => 0
                ];
            }
            
            $reviewerStats[$key]['review_count']++;
            $reviewerStats[$key]['total_rating'] += $review->getRating();
        }
        
        foreach ($reviewerStats as &$stats) {
            $stats['average_rating'] = round($stats['total_rating'] / $stats['review_count'], 2);
        }
        
        uasort($reviewerStats, function($a, $b) {
            return $b['review_count'] <=> $a['review_count'];
        });
        
        return array_slice(array_values($reviewerStats), 0, $limit);
    }
}