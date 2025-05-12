<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Tag;

/**
 * Typovaná kolekce pro tagy
 * 
 * @extends Collection<Tag>
 */
class TagCollection extends Collection
{
    /**
     * Filtruje tagy podle částečné shody názvu
     * 
     * @param string $name
     * @return self
     */
    public function filterByNameContains(string $name): self
    {
        return $this->filter(function(Tag $tag) use ($name) {
            return stripos($tag->name, $name) !== false;
        });
    }
    
    /**
     * Seřadí tagy podle jména
     * 
     * @param string $direction
     * @return self
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Tag $a, Tag $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->name, $b->name)
                : strcmp($b->name, $a->name);
        });
    }
    
    /**
     * Vrátí pole se slugy tagů
     * 
     * @return array<string>
     */
    public function getSlugs(): array
    {
        return $this->map(function(Tag $tag) {
            return $tag->slug;
        });
    }
    
    /**
     * Konvertuje kolekci tagů na formát pro tagCloud
     * 
     * @return array
     */
    public function toTagCloud(): array
    {
        return $this->map(function(Tag $tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'weight' => 1 // Výchozí váha
            ];
        });
    }
}