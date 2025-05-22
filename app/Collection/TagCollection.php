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
}