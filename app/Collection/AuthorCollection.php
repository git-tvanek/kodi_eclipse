<?php

namespace App\Collection;

use App\Entity\Author;

/**
 * Typovaná kolekce pro autory s ověřenými metodami
 * 
 * @extends Collection<Author>
 */
class AuthorCollection extends Collection
{
    /**
     * ✅ Používá existující metody z Author entity
     * Author má getAddons() -> Collection, takže můžeme použít count()
     */
    public function filterByMinAddonCount(int $minCount): self
    {
        return $this->filter(function(Author $author) use ($minCount) {
            return $author->getAddons()->count() >= $minCount;
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Author entity
     */
    public function filterWithWebsite(): self
    {
        return $this->filter(function(Author $author): bool {
            $website = $author->getWebsite();
            return $website !== null && trim($website) !== '';
        });
    }
    
    /**
     * ✅ Používá existující getter metody z Author entity
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Author $a, Author $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getName(), $b->getName())
                : strcmp($b->getName(), $a->getName());
        });
    }
    
    /**
     * ✅ Seřadí podle počtu doplňků - používá existující metody
     */
    public function sortByAddonCount(string $direction = 'DESC'): self
    {
        return $this->sort(function(Author $a, Author $b) use ($direction) {
            $countA = $a->getAddons()->count();
            $countB = $b->getAddons()->count();
            
            return $direction === 'DESC' ? $countB <=> $countA : $countA <=> $countB;
        });
    }
    
    /**
     * ✅ Získá autory jako pole s počty doplňků
     */
    public function toArrayWithAddonCounts(): array
    {
        return $this->map(function(Author $author): array {
            return [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'email' => $author->getEmail(),
                'website' => $author->getWebsite(),
                'addon_count' => $author->getAddons()->count()
            ];
        });
    }
}