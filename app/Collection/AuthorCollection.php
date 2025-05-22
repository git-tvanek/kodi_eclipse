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

      /**
     * 🏆 Nejproduktivnější autoři
     */
    public function getMostProductive(int $limit = 10): self
    {
        return $this->sortByAddonCount('DESC')->take($limit);
    }

    /**
     * ⭐ Autoři s nejvyšším průměrným hodnocením
     */
    public function getTopRated(int $minAddons = 3, int $limit = 10): self
    {
        return $this->filterByMinAddonCount($minAddons)
            ->sort(function(Author $a, Author $b) {
                $avgA = $this->calculateAverageRating($a);
                $avgB = $this->calculateAverageRating($b);
                return $avgB <=> $avgA;
            })
            ->take($limit);
    }

    /**
     * 🔥 Aktivní autoři
     */
    public function getActive(int $days = 30): self
    {
        $since = new \DateTime("-{$days} days");
        
        return $this->filter(function(Author $author) use ($since) {
            foreach ($author->getAddons() as $addon) {
                if ($addon->getCreatedAt() >= $since || $addon->getUpdatedAt() >= $since) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * 📈 Seřadit podle celkových stažení
     */
    public function sortByTotalDownloads(string $direction = 'DESC'): self
    {
        return $this->sort(function(Author $a, Author $b) use ($direction) {
            $downloadsA = $this->calculateTotalDownloads($a);
            $downloadsB = $this->calculateTotalDownloads($b);
            
            return $direction === 'DESC' ? $downloadsB <=> $downloadsA : $downloadsA <=> $downloadsB;
        });
    }

    /**
     * 🔍 Vyhledávání podle kontaktu
     */
    public function searchByContact(string $query): self
    {
        if (empty(trim($query))) {
            return $this;
        }
        
        return $this->filter(function(Author $author) use ($query) {
            $searchableText = strtolower($author->getName() . ' ' . ($author->getEmail() ?? ''));
            return str_contains($searchableText, strtolower(trim($query)));
        });
    }

    /**
     * 🌟 Top contributors s detailními statistikami
     */
    public function getTopContributors(int $limit = 10): array
    {
        return $this->sort(function(Author $a, Author $b) {
            $scoreA = $this->calculateContributorScore($a);
            $scoreB = $this->calculateContributorScore($b);
            return $scoreB <=> $scoreA;
        })
        ->take($limit)
        ->map(function(Author $author) {
            return [
                'author' => $author,
                'stats' => [
                    'addon_count' => $author->getAddons()->count(),
                    'total_downloads' => $this->calculateTotalDownloads($author),
                    'average_rating' => $this->calculateAverageRating($author),
                    'contributor_score' => $this->calculateContributorScore($author)
                ]
            ];
        });
    }

    // ========== POMOCNÉ METODY ==========

    private function calculateTotalDownloads(Author $author): int
    {
        $total = 0;
        foreach ($author->getAddons() as $addon) {
            $total += $addon->getDownloadsCount();
        }
        return $total;
    }

    private function calculateAverageRating(Author $author): float
    {
        $addons = $author->getAddons();
        if ($addons->count() === 0) return 0;
        
        $totalRating = 0;
        foreach ($addons as $addon) {
            $totalRating += $addon->getRating();
        }
        
        return round($totalRating / $addons->count(), 2);
    }

    private function calculateContributorScore(Author $author): float
    {
        $addonCount = $author->getAddons()->count();
        $totalDownloads = $this->calculateTotalDownloads($author);
        $avgRating = $this->calculateAverageRating($author);
        
        return ($addonCount * 10) + ($avgRating * 100) + log($totalDownloads + 1);
    }
}