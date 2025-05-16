<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Tag;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář tagů
 * 
 * @extends BaseRepositoryInterface<Tag>
 */
interface ITagRepository extends IBaseRepository
{
    /**
     * Najde tag podle slugu
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag;
    
    /**
     * Najde nebo vytvoří tag
     * 
     * @param string $name
     * @return int ID tagu
     */
    public function findOrCreate(string $name): int;
    
    /**
     * Vytvoří nový tag
     * 
     * @param Tag $tag
     * @return int
     */
    public function create(Tag $tag): int;

    /**
    * Aktualizuje existující tag
    * 
    * @param Tag $tag Tag k aktualizaci
    * @return int ID aktualizovaného tagu
    */
    public function update(Tag $tag): int;
    
    /**
     * Získá tagy s počty doplňků
     * 
     * @return array
     */
    public function getTagsWithCounts(): array;
    
    /**
     * Najde doplňky podle tagu
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde tagy s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Tag>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde související tagy
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array;
    
    /**
     * Získá trendové tagy (tagy s nedávnou aktivitou)
     * 
     * @param int $days Počet dní zpět
     * @param int $limit Maximální počet tagů k vrácení
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array;
    
    /**
     * Generuje vážený tag cloud
     * 
     * @param int $limit Maximální počet tagů k zahrnutí
     * @param int|null $categoryId Volitelné ID kategorie pro filtrování
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array;
    
    /**
     * Najde tagy podle více kategorií
     * 
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function findTagsByCategories(array $categoryIds, int $limit = 20): array;

    /**
 * Najde tagy přiřazené k doplňku
 * 
 * @param int $addonId ID doplňku
 * @return Collection<Tag>
 */
public function findByAddon(int $addonId): Collection;
}