<?php

declare(strict_types=1);

namespace App\Service;

use App\Collection\Collection;
use App\Entity\Tag;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro tagy
 * 
 * @extends IBaseService<Tag>
 */
interface ITagService extends IBaseService
{
    /**
     * Vytvoří nový tag
     * 
     * @param array $data
     * @return int ID vytvořeného tagu
     */
    public function create(array $data): int;

    /**
    * Aktualizuje existující tag
    * 
    * @param int $id ID tagu
    * @param array $data Data pro aktualizaci
    * @return int ID aktualizovaného tagu
    */
    public function update(int $id, array $data): int;
    
    /**
     * Vytvoří tag pouze s názvem
     * 
     * @param string $name
     * @return int ID vytvořeného tagu
     */
    public function createWithName(string $name): int;
    
    /**
     * Vytvoří více tagů najednou
     * 
     * @param array $names Pole názvů tagů
     * @return array Pole ID vytvořených tagů
     */
    public function createBatch(array $names): array;
    
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
     * Získá tagy s počty
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
     * Najde související tagy
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array;
    
    /**
     * Získá trendové tagy
     * 
     * @param int $days Počet dní dozadu
     * @param int $limit Maximální počet tagů k vrácení
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array;
    
    /**
     * Vygeneruje vážený tag cloud
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
    * @param int $addonId
    * @return Collection<Tag>
    */
    public function findByAddon(int $addonId): Collection;
}