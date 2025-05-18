<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\TagFactory;

/**
 * Implementace služby pro tagy
 * 
 * @extends BaseService<Tag>
 * @implements ITagService
 */
class TagService extends BaseService implements ITagService
{
    /** @var TagRepository */
    private TagRepository $tagRepository;
    
    /** @var TagFactory */
    private TagFactory $tagFactory;
    
    /**
     * Konstruktor
     * 
     * @param TagRepository $tagRepository
     * @param TagFactory $tagFactory
     */
    public function __construct(
        TagRepository $tagRepository,
        TagFactory $tagFactory
    ) {
        parent::__construct();
        $this->tagRepository = $tagRepository;
        $this->tagFactory = $tagFactory;
        $this->entityClass = Tag::class;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return TagRepository
     */
    protected function getRepository(): TagRepository
    {
        return $this->tagRepository;
    }
    
    /**
     * Vytvoří nový tag
     * 
     * @param array $data
     * @return int ID vytvořeného tagu
     */
    public function create(array $data): int
    {
        $tag = $this->tagFactory->create($data);
        return $this->tagRepository->create($tag);
    }

    /**
     * Aktualizuje existující tag
    * 
    * @param int $id ID tagu
     * @param array $data Data pro aktualizaci
     * @return int ID aktualizovaného tagu
     */
    public function update(int $id, array $data): int
    {
    // Získání existujícího tagu
    $tag = $this->findById($id);
    if (!$tag) {
        throw new \Exception("Tag s ID {$id} nebyl nalezen.");
    }
    
    // Aktualizace vlastností tagu
    if (isset($data['name'])) {
        $tag->name = $data['name'];
    }
    
    if (isset($data['slug'])) {
        $tag->slug = $data['slug'];
    } else if (isset($data['name'])) {
        // Automatické vytvoření slugu, pokud byl změněn název
        $tag->slug = \Nette\Utils\Strings::webalize($data['name']);
    }
    
    // Použití TagRepository pro aktualizaci
    return $this->tagRepository->update($tag);
    }
    
    /**
     * Vytvoří tag pouze s názvem
     * 
     * @param string $name
     * @return int ID vytvořeného tagu
     */
    public function createWithName(string $name): int
    {
        $tag = $this->tagFactory->createWithName($name);
        return $this->tagRepository->create($tag);
    }
    
    /**
     * Vytvoří více tagů najednou
     * 
     * @param array $names Pole názvů tagů
     * @return array Pole ID vytvořených tagů
     */
    public function createBatch(array $names): array
    {
        $ids = [];
        $tags = $this->tagFactory->createBatch($names);
        
        foreach ($tags as $tag) {
            $ids[] = $this->tagRepository->create($tag);
        }
        
        return $ids;
    }
    
    /**
     * Najde tag podle slugu
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->tagRepository->findBySlug($slug);
    }
    
    /**
     * Najde nebo vytvoří tag
     * 
     * @param string $name
     * @return int ID tagu
     */
    public function findOrCreate(string $name): int
    {
        return $this->tagRepository->findOrCreate($name);
    }
    
    /**
     * Získá tagy s počty
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
    {
        return $this->tagRepository->getTagsWithCounts();
    }
    
    /**
     * Najde doplňky podle tagu
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->tagRepository->findAddonsByTag($tagId, $page, $itemsPerPage);
    }
    
    /**
     * Najde související tagy
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array
    {
        return $this->tagRepository->findRelatedTags($tagId, $limit);
    }
    
    /**
     * Získá trendové tagy
     * 
     * @param int $days Počet dní dozadu
     * @param int $limit Maximální počet tagů k vrácení
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        return $this->tagRepository->getTrendingTags($days, $limit);
    }
    
    /**
     * Vygeneruje vážený tag cloud
     * 
     * @param int $limit Maximální počet tagů k zahrnutí
     * @param int|null $categoryId Volitelné ID kategorie pro filtrování
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        return $this->tagRepository->generateTagCloud($limit, $categoryId);
    }
    
    /**
     * Najde tagy podle více kategorií
     * 
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function findTagsByCategories(array $categoryIds, int $limit = 20): array
    {
        return $this->tagRepository->findTagsByCategories($categoryIds, $limit);
    }

 /**
 * Najde tagy přiřazené k doplňku
 * 
 * @param int $addonId ID doplňku
 * @return Collection<Tag>
 */
public function findByAddon(int $addonId): Collection
{
    return $this->tagRepository->findByAddon($addonId);
}
}