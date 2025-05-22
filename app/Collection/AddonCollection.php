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
/**
     * 🔥 Získá nejstahovanější doplňky
     */
    public function getMostDownloaded(int $limit = 10): self
    {
        return $this->sortByDownloads('DESC')->take($limit);
    }

    /**
     * ⭐ Získá nejlépe hodnocené doplňky
     */
    public function getBestRated(float $minRating = 4.0, int $limit = 10): self
    {
        return $this->filterByMinRating($minRating)
                   ->sortByRating('DESC')
                   ->take($limit);
    }

    /**
     * 🆕 Získá nové doplňky za posledních N dní
     */
    public function getRecent(int $days = 7): self
    {
        $since = new \DateTime("-{$days} days");
        return $this->filter(function(Addon $addon) use ($since) {
            return $addon->getCreatedAt() >= $since;
        })->sortBy('created_at', 'DESC');
    }

    /**
     * 📈 Získá trendové doplňky
     */
    public function getTrending(int $limit = 20): self
    {
        return $this->filterByMinRating(3.0)
                   ->filter(function(Addon $addon) {
                       return $addon->getDownloadsCount() > 100;
                   })
                   ->getRecent(30)
                   ->sortByDownloads('DESC')
                   ->take($limit);
    }

    /**
     * 📁 Filtruje podle více kategorií najednou
     */
    public function filterByCategories(array $categoryIds): self
    {
        return $this->filter(function(Addon $addon) use ($categoryIds) {
            return in_array($addon->getCategory()->getId(), $categoryIds);
        });
    }

    /**
     * 👥 Filtruje podle více autorů najednou
     */
    public function filterByAuthors(array $authorIds): self
    {
        return $this->filter(function(Addon $addon) use ($authorIds) {
            return in_array($addon->getAuthor()->getId(), $authorIds);
        });
    }

    /**
     * 🔍 Vyhledává v názvu a popisu
     */
    public function searchInContent(string $query): self
    {
        if (empty(trim($query))) {
            return $this;
        }
        
        $keywords = preg_split('/\s+/', trim($query));
        
        return $this->filter(function(Addon $addon) use ($keywords) {
            $content = strtolower($addon->getName() . ' ' . ($addon->getDescription() ?? ''));
            
            foreach ($keywords as $keyword) {
                if (str_contains($content, strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * 🎮 Pokročilé filtrování pro Kodi verze
     */
    public function compatibleWithKodiRange(string $minVersion, string $maxVersion): self
    {
        return $this->filter(function(Addon $addon) use ($minVersion, $maxVersion) {
            $addonMin = $addon->getKodiVersionMin() ?: '0.0';
            $addonMax = $addon->getKodiVersionMax() ?: '999.0';
            
            return version_compare($addonMin, $maxVersion, '<=') && 
                   version_compare($addonMax, $minVersion, '>=');
        });
    }

    /**
     * 🎯 Doporučené doplňky
     */
    public function getRecommended(int $limit = 10): self
    {
        return $this->filter(function(Addon $addon) {
                return $addon->getRating() >= 3.5 && $addon->getDownloadsCount() >= 50;
            })
            ->sort(function(Addon $a, Addon $b) {
                $scoreA = $a->getRating() * log($a->getDownloadsCount() + 1);
                $scoreB = $b->getRating() * log($b->getDownloadsCount() + 1);
                return $scoreB <=> $scoreA;
            })
            ->take($limit);
    }

    /**
     * 📊 Rychlé metriky pro dashboard
     */
    public function getQuickMetrics(): array
    {
        return [
            'total' => $this->count(),
            'avg_rating' => round($this->getAverageRating(), 2),
            'total_downloads' => $this->getTotalDownloads(),
            'categories_count' => $this->unique(function(Addon $addon) { 
                return $addon->getCategory()->getId(); 
            })->count(),
            'authors_count' => $this->unique(function(Addon $addon) { 
                return $addon->getAuthor()->getId(); 
            })->count()
        ];
    }

    /**
     * 🎯 Export pro API
     */
    public function toApiFormat(): array
    {
        return [
            'items' => $this->map(function(Addon $addon) {
                return [
                    'id' => $addon->getId(),
                    'name' => $addon->getName(),
                    'slug' => $addon->getSlug(),
                    'version' => $addon->getVersion(),
                    'rating' => $addon->getRating(),
                    'downloads' => $addon->getDownloadsCount(),
                    'author' => $addon->getAuthor()->getName(),
                    'category' => $addon->getCategory()->getName(),
                    'icon_url' => $addon->getIconUrl(),
                    'created_at' => $addon->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }),
            'meta' => $this->getQuickMetrics()
        ];
    }
    
}