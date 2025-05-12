<?php

declare(strict_types=1);

namespace App\Collection;

use App\Model\Addon;

/**
 * Typovaná kolekce pro doplňky
 * 
 * @extends Collection<Addon>
 */
class AddonCollection extends Collection
{
    /**
     * Vrátí kolekci seřazenou podle počtu stažení
     * 
     * @param string $direction
     * @return self
     */
    public function sortByDownloads(string $direction = 'DESC'): self
    {
        return $this->sort(function(Addon $a, Addon $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->downloads_count <=> $a->downloads_count
                : $a->downloads_count <=> $b->downloads_count;
        });
    }
    
    /**
     * Vrátí kolekci seřazenou podle hodnocení
     * 
     * @param string $direction
     * @return self
     */
    public function sortByRating(string $direction = 'DESC'): self
    {
        return $this->sort(function(Addon $a, Addon $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->rating <=> $a->rating
                : $a->rating <=> $b->rating;
        });
    }
    
    /**
     * Filtruje doplňky podle kategorie
     * 
     * @param int $categoryId
     * @return self
     */
    public function filterByCategory(int $categoryId): self
    {
        return $this->filter(function(Addon $addon) use ($categoryId) {
            return $addon->category_id === $categoryId;
        });
    }
    
    /**
     * Vrátí doplňky splňující minimální hodnocení
     * 
     * @param float $minRating
     * @return self
     */
    public function filterByMinRating(float $minRating): self
    {
        return $this->filter(function(Addon $addon) use ($minRating) {
            return $addon->rating >= $minRating;
        });
    }
    
    /**
     * Vrátí doplňky kompatibilní s danou verzí Kodi
     * 
     * @param string $version
     * @return self
     */
    public function filterByKodiVersion(string $version): self
    {
        return $this->filter(function(Addon $addon) use ($version) {
            if ($addon->kodi_version_min && version_compare($version, $addon->kodi_version_min, '<')) {
                return false;
            }
            
            if ($addon->kodi_version_max && version_compare($version, $addon->kodi_version_max, '>')) {
                return false;
            }
            
            return true;
        });
    }
}