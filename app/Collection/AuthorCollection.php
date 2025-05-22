<?php

declare(strict_types=1);

namespace App\Collection;

use App\Entity\Author;

/**
 * Typovaná kolekce pro autory
 * 
 * @extends Collection<Author>
 */
class AuthorCollection extends Collection
{
    /**
     * Filtruje autory podle počtu doplňků
     * 
     * @param int $minCount
     * @return self
     */
    public function filterByMinAddonCount(int $minCount): self
    {
        return $this->filter(function(Author $author) use ($minCount) {
            // Zde by ideálně měla být logika pro spočítání doplňků autora,
            // ale to by vyžadovalo přístup k repozitáři nebo dodatečná data.
            // Ukázková implementace předpokládá, že máme počet doplňků v property
            return $author->addon_count >= $minCount;
        });
    }
    
    /**
     * Filtruje autory, kteří mají webovou stránku
     * 
     * @return self
     */
    public function filterWithWebsite(): self
    {
        return $this->filter(function(Author $author) {
            return !empty($author->website);
        });
    }
    
    /**
     * Seřadí autory podle jména
     * 
     * @param string $direction
     * @return self
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Author $a, Author $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->name, $b->name)
                : strcmp($b->name, $a->name);
        });
    }
}