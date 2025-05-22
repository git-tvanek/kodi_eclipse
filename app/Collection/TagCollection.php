<?php

namespace App\Collection;

use App\Entity\Tag;

/**
 * Typovaná kolekce pro tagy s opravenými metodami
 * 
 * @extends Collection<Tag>
 */
class TagCollection extends Collection
{
    /**
     * ✅ OPRAVENO: Používá getter metody
     */
    public function filterByNameContains(string $name): self
    {
        return $this->filter(function(Tag $tag) use ($name) {
            return stripos($tag->getName(), $name) !== false;
        });
    }
    
    /**
     * ✅ OPRAVENO: Používá getter metody
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Tag $a, Tag $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getName(), $b->getName())
                : strcmp($b->getName(), $a->getName());
        });
    }
    
    /**
     * ✅ OPRAVENO: Používá getter metody
     */
    public function getSlugs(): array
    {
        return $this->map(function(Tag $tag): string {
            return $tag->getSlug();
        });
    }
    
    /**
     * ✅ OPRAVENO: Používá getter metody a lepší strukturu
     */
    public function toTagCloud(): array
    {
        return $this->map(function(Tag $tag): array {
            return [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
                'weight' => method_exists($tag, 'getAddonCount') ? $tag->getAddons()->count() : 1
            ];
        });
    }
    
    /**
     * ✅ NOVÁ METODA: Získá tagy jako key-value pairs
     */
    public function toIdNamePairs(): array
    {
        return $this->reduce(function(array $pairs, Tag $tag): array {
            $pairs[$tag->getId()] = $tag->getName();
            return $pairs;
        }, []);
    }
    
    /**
     * ✅ NOVÁ METODA: Filtruje podle minimálního počtu doplňků
     */
    public function filterByMinAddonCount(int $minCount): self
    {
        return $this->filter(function(Tag $tag) use ($minCount): bool {
            return method_exists($tag, 'getAddonCount') 
                ? $tag->getAddons()->count() >= $minCount
                : $tag->getAddons()->count() >= $minCount;
        });
    }

     /**
     * 🔥 Nejpopulárnější tagy
     */
    public function getMostPopular(int $limit = 20): self
    {
        return $this->sort(function(Tag $a, Tag $b) {
                return $b->getAddons()->count() <=> $a->getAddons()->count();
            })
            ->take($limit);
    }

    /**
     * 📈 Trendové tagy
     */
    public function getTrending(int $days = 30, int $limit = 15): self
    {
        $since = new \DateTime("-{$days} days");
        
        return $this->filter(function(Tag $tag) use ($since) {
                foreach ($tag->getAddons() as $addon) {
                    if ($addon->getCreatedAt() >= $since) {
                        return true;
                    }
                }
                return false;
            })
            ->getMostPopular($limit);
    }

    /**
     * 🔍 Vyhledávání podle názvu
     */
    public function searchByName(string $query): self
    {
        if (empty(trim($query))) {
            return $this;
        }
        
        return $this->filter(function(Tag $tag) use ($query) {
            return str_contains(strtolower($tag->getName()), strtolower(trim($query)));
        });
    }

    /**
     * 📁 Tagy v konkrétní kategorii
     */
    public function usedInCategory(int $categoryId): self
    {
        return $this->filter(function(Tag $tag) use ($categoryId) {
            foreach ($tag->getAddons() as $addon) {
                if ($addon->getCategory()->getId() === $categoryId) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * 🎨 Tag cloud s váhami
     */
    public function generateTagCloud(int $minWeight = 1, int $maxWeight = 10): array
    {
        $tagData = $this->map(function(Tag $tag) {
            return [
                'tag' => $tag,
                'count' => $tag->getAddons()->count()
            ];
        })->filter(function($item) {
            return $item['count'] > 0;
        });
        
        if (empty($tagData)) return [];
        
        $counts = array_column($tagData, 'count');
        $maxCount = max($counts);
        $minCount = min($counts);
        $range = max(1, $maxCount - $minCount);
        
        return array_map(function($item) use ($minCount, $range, $minWeight, $maxWeight) {
            $normalizedWeight = $minWeight + (($item['count'] - $minCount) / $range) * ($maxWeight - $minWeight);
            
            return [
                'tag' => $item['tag'],
                'count' => $item['count'],
                'weight' => round($normalizedWeight),
                'css_class' => 'tag-weight-' . round($normalizedWeight),
                'font_size' => round(10 + ($normalizedWeight / $maxWeight) * 20)
            ];
        }, $tagData);
    }

    /**
     * 📊 Analýza použití tagů
     */
    public function getUsageAnalysis(): array
    {
        $totalTags = $this->count();
        $usedTags = $this->filter(function(Tag $tag) {
            return $tag->getAddons()->count() > 0;
        });
        
        // ✅ OPRAVA: Manuální výpočet průměru místo neexistující average()
        $averageUsage = 0;
        if (!$usedTags->isEmpty()) {
            $totalUsage = 0;
            foreach ($usedTags as $tag) {
                $totalUsage += $tag->getAddons()->count();
            }
            $averageUsage = round($totalUsage / $usedTags->count(), 1);
        }
        
        return [
            'total_tags' => $totalTags,
            'used_tags' => $usedTags->count(),
            'unused_tags' => $totalTags - $usedTags->count(),
            'usage_ratio' => $totalTags > 0 ? round($usedTags->count() / $totalTags, 2) : 0,
            'average_usage' => $averageUsage, // ✅ Správný výpočet
            'most_popular' => $this->getMostPopular(10)->toArray(),
            'trending' => $this->getTrending(30, 10)->toArray()
        ];
    }
}
