<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use App\Repository\TagRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\Interface\IFactoryManager;

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
    
    /**
     * Konstruktor
     */
    public function __construct(
        TagRepository $tagRepository,
        IFactoryManager $factoryManager
    ) {
        parent::__construct($factoryManager);
        $this->tagRepository = $tagRepository;
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
        $tag = $this->factoryManager->createTag($data);
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
        
        // Aktualizace tagu pomocí továrny
        $updatedTag = $this->factoryManager->getTagFactory()->createFromExisting($tag, $data, false);
        return $this->tagRepository->update($updatedTag);
    }
    
    /**
     * Vytvoří tag pouze s názvem
     * 
     * @param string $name
     * @return int ID vytvořeného tagu
     */
    public function createWithName(string $name): int
    {
        $data = [
            'name' => $name,
            'slug' => \Nette\Utils\Strings::webalize($name)
        ];
        
        return $this->create($data);
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
        
        foreach ($names as $name) {
            $ids[] = $this->createWithName($name);
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
        $slug = \Nette\Utils\Strings::webalize($name);
        $tag = $this->findBySlug($slug);
        
        if ($tag) {
            return $tag->getId();
        }
        
        return $this->createWithName($name);
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