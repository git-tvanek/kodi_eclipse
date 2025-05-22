<?php
namespace App\Collection;

use App\Entity\Screenshot;

/**
 * Typovaná kolekce pro screenshoty
 * 
 * @extends Collection<Screenshot>
 */
class ScreenshotCollection extends Collection
{
    /**
     * Seřadí podle pořadí (sort_order)
     */
    public function sortBySortOrder(string $direction = 'ASC'): self
    {
        return $this->sort(function(Screenshot $a, Screenshot $b) use ($direction) {
            return $direction === 'ASC' 
                ? $a->getSortOrder() <=> $b->getSortOrder()
                : $b->getSortOrder() <=> $a->getSortOrder();
        });
    }
    
    /**
     * Filtruje podle doplňku
     */
    public function filterByAddon(int $addonId): self
    {
        return $this->filter(function(Screenshot $screenshot) use ($addonId): bool {
            return $screenshot->getAddon()->getId() === $addonId;
        });
    }
    
    /**
     * Filtruje screenshoty s popisem
     */
    public function filterWithDescription(): self
    {
        return $this->filter(function(Screenshot $screenshot): bool {
            $description = $screenshot->getDescription();
            return $description !== null && trim($description) !== '';
        });
    }
    
    /**
     * Získá URL obrázků jako pole
     */
    public function getUrls(): array
    {
        return $this->map(function(Screenshot $screenshot): string {
            return $screenshot->getUrl();
        });
    }
    
    /**
     * Získá první screenshot (hlavní)
     */
    public function getMainScreenshot(): ?Screenshot
    {
        return $this->sortBySortOrder('ASC')->first();
    }
    
    /**
     * Přeřadí screenshoty podle nového pořadí
     */
    public function reorder(array $newOrder): self
    {
        $reordered = [];
        
        foreach ($newOrder as $index => $screenshotId) {
            $screenshot = $this->findFirst(function(Screenshot $s) use ($screenshotId): bool {
                return $s->getId() === $screenshotId;
            });
            
            if ($screenshot) {
                $reordered[] = $screenshot;
            }
        }
        
        return new static($reordered);
    }
    
    /**
     * Seskupí podle doplňků
     */
    public function groupByAddon(): array
    {
        return $this->groupBy('addon');
    }

    /**
     * 📸 Screenshoty s popisem
     */
    public function getWithDescription(): self
    {
        return $this->filterWithDescription();
    }

    /**
     * 🎨 Screenshoty bez popisu
     */
    public function getWithoutDescription(): self
    {
        return $this->filter(function(Screenshot $screenshot) {
            $description = $screenshot->getDescription();
            return $description === null || trim($description) === '';
        });
    }

    /**
     * 🔄 Přeřadí screenshoty
     */
    public function reorderScreenshots(array $screenshotIds): self
    {
        $reordered = [];
        
        foreach ($screenshotIds as $index => $screenshotId) {
            $screenshot = $this->findFirst(function(Screenshot $s) use ($screenshotId) {
                return $s->getId() === $screenshotId;
            });
            
            if ($screenshot) {
                $reordered[] = $screenshot;
            }
        }
        
        return new static($reordered);
    }

    /**
     * 📊 Screenshoty se statistikami
     */
    public function withStats(): array
    {
        return [
            'total_count' => $this->count(),
            'with_description' => $this->getWithDescription()->count(),
            'without_description' => $this->getWithoutDescription()->count(),
            'description_ratio' => $this->count() > 0 ? 
                round($this->getWithDescription()->count() / $this->count(), 2) : 0,
            'main_screenshot' => $this->getMainScreenshot(),
            'by_addon' => $this->groupByAddon()
        ];
    }

    /**
     * 🎯 Export pro galerii
     */
    public function toGalleryFormat(): array
    {
        return $this->sortBySortOrder('ASC')
                   ->map(function(Screenshot $screenshot) {
                       return [
                           'id' => $screenshot->getId(),
                           'url' => $screenshot->getUrl(),
                           'description' => $screenshot->getDescription(),
                           'sort_order' => $screenshot->getSortOrder(),
                           'addon_name' => $screenshot->getAddon()->getName(),
                           'is_main' => $screenshot->getSortOrder() === 0
                       ];
                   });
    }
}