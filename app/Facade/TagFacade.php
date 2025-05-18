<?php

declare(strict_types=1);

namespace App\Facade;

use App\Collection\PaginatedCollection;
use App\Entity\Tag;
use App\Service\ITagService;

/**
 * Fasáda pro práci s tagy
 */
class TagFacade implements IFacade
{
    /** @var ITagService */
    private ITagService $tagService;
    
    /**
     * Konstruktor
     * 
     * @param ITagService $tagService
     */
    public function __construct(ITagService $tagService)
    {
        $this->tagService = $tagService;
    }
    
    /**
     * Vytvoří nový tag
     * 
     * @param string $name Název tagu
     * @return int ID vytvořeného tagu
     */
    public function createTag(string $name): int
    {
        return $this->tagService->createWithName($name);
    }
    
    /**
     * Vytvoří více tagů najednou
     * 
     * @param array $names Pole názvů tagů
     * @return array Pole ID vytvořených tagů
     */
    public function createMultipleTags(array $names): array
    {
        return $this->tagService->createBatch($names);
    }
    
    /**
     * Najde tag podle ID
     * 
     * @param int $tagId ID tagu
     * @return Tag|null
     */
    public function getTag(int $tagId): ?Tag
    {
        return $this->tagService->findById($tagId);
    }
    
    /**
     * Najde tag podle slugu
     * 
     * @param string $slug Slug tagu
     * @return Tag|null
     */
    public function getTagBySlug(string $slug): ?Tag
    {
        return $this->tagService->findBySlug($slug);
    }
    
    /**
     * Najde nebo vytvoří tag
     * 
     * @param string $name Název tagu
     * @return int ID tagu
     */
    public function findOrCreateTag(string $name): int
    {
        return $this->tagService->findOrCreate($name);
    }
    
    /**
     * Získá všechny tagy s počty použití
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
    {
        return $this->tagService->getTagsWithCounts();
    }
    
    /**
     * Najde doplňky s daným tagem
     * 
     * @param int $tagId ID tagu
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection
     */
    public function getAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->tagService->findAddonsByTag($tagId, $page, $itemsPerPage);
    }
    
    /**
     * Najde související tagy
     * 
     * @param int $tagId ID tagu
     * @param int $limit Počet tagů
     * @return array
     */
    public function getRelatedTags(int $tagId, int $limit = 10): array
    {
        return $this->tagService->findRelatedTags($tagId, $limit);
    }
    
    /**
     * Získá trendové tagy
     * 
     * @param int $days Počet dní
     * @param int $limit Počet tagů
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        return $this->tagService->getTrendingTags($days, $limit);
    }
    
    /**
     * Vygeneruje tag cloud
     * 
     * @param int $limit Počet tagů
     * @param int|null $categoryId ID kategorie pro filtrování
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        return $this->tagService->generateTagCloud($limit, $categoryId);
    }
    
    /**
     * Získá tagy podle kategorií
     * 
     * @param array $categoryIds Pole ID kategorií
     * @param int $limit Počet tagů
     * @return array
     */
    public function getTagsByCategories(array $categoryIds, int $limit = 20): array
    {
        return $this->tagService->findTagsByCategories($categoryIds, $limit);
    }

    /**
    * Aktualizuje existující tag
    * 
    * @param int $id ID tagu
    * @param array $data Data pro aktualizaci
    * @return int ID aktualizovaného tagu
    */
    public function updateTag(int $id, array $data): int
    {
    return $this->tagService->update($id, $data);
    }
    
    /**
     * Smaže tag
     * 
     * @param int $tagId ID tagu
     * @return bool
     */
    public function deleteTag(int $tagId): bool
    {
        return $this->tagService->delete($tagId);
    }
}