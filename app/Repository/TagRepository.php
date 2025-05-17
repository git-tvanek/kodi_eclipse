<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\Addon;
use App\Entity\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ITagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Strings;

/**
 * Repozitář pro práci s tagy
 * 
 * @extends BaseRepository<Tag>
 */
class TagRepository extends BaseRepository implements ITagRepository
{
    protected string $defaultAlias = 't';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Tag::class);
    }
    
    /**
     * Vytvoří typovanou kolekci tagů
     * 
     * @param array<Tag> $entities
     * @return Collection<Tag>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde tag podle slugu
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    /**
     * Najde nebo vytvoří tag
     * 
     * @param string $name
     * @return int ID tagu
     */
    public function findOrCreate(string $name): int
    {
        $slug = Strings::webalize($name);
        $tag = $this->findBySlug($slug);
        
        if ($tag) {
            return $tag->getId();
        }
        
        return $this->transaction(function() use ($name, $slug) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setSlug($slug);
            
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
            
            return $tag->getId();
        });
    }
    
    /**
     * Vytvoří nový tag
     * 
     * @param Tag $tag
     * @return int ID nového tagu
     */
    public function create(Tag $tag): int
    {
        // Generate slug if not set
        if (!$tag->getSlug()) {
            $tag->setSlug(Strings::webalize($tag->getName()));
        }
        
        return $this->save($tag);
    }
    
    /**
     * Aktualizuje existující tag
     * 
     * @param Tag $tag
     * @return int ID aktualizovaného tagu
     */
    public function update(Tag $tag): int
    {
        // Generate slug if not set
        if (!$tag->getSlug()) {
            $tag->setSlug(Strings::webalize($tag->getName()));
        }
        
        return $this->updateEntity($tag);
    }
    
    /**
     * Získá tagy s počty doplňků
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", "COUNT(a.id) as addonCount")
           ->from(Tag::class, $this->defaultAlias)
           ->leftJoin("$this->defaultAlias.addons", 'a')
           ->groupBy("$this->defaultAlias.id")
           ->orderBy("$this->defaultAlias.name", 'ASC');
        
        $result = $qb->getQuery()->getResult();
        
        $tags = [];
        foreach ($result as $row) {
            $tag = $row[0];
            $addonCount = $row['addonCount'];
            
            $tagData = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
                'addon_count' => (int)$addonCount
            ];
            
            $tags[] = $tagData;
        }
        
        return $tags;
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
        $tag = $this->find($tagId);
        if (!$tag) {
            return new PaginatedCollection(new Collection([]), 0, $page, $itemsPerPage, 0);
        }
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
           ->from(Addon::class, 'a')
           ->join('a.tags', 't')
           ->where('t = :tag')
           ->setParameter('tag', $tag)
           ->orderBy('a.name', 'ASC');
        
        // Create paginator
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
        
        $total = count($paginator);
        $pages = (int)ceil($total / $itemsPerPage);
        
        $addons = iterator_to_array($paginator->getIterator());
        
        return new PaginatedCollection(
            new Collection($addons),
            $total,
            $page,
            $itemsPerPage,
            $pages
        );
    }
    
    /**
     * Vyhledá tagy podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Tag>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        // Apply standard filters
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        // Apply custom filters specific to tags
        if (isset($filters['min_addons']) && $filters['min_addons'] !== null && $filters['min_addons'] > 0) {
            $qb->join("$this->defaultAlias.addons", 'a')
               ->groupBy("$this->defaultAlias.id")
               ->having('COUNT(a.id) >= :minAddons')
               ->setParameter('minAddons', $filters['min_addons']);
        }
        
        if (isset($filters['category_id']) && $filters['category_id'] !== null) {
            $qb->join("$this->defaultAlias.addons", 'a')
               ->andWhere('a.category = :category')
               ->setParameter('category', $this->entityManager->getReference(Category::class, $filters['category_id']));
        }
        
        // Apply sorting
        if ($this->hasProperty($sortBy)) {
            $qb->orderBy("$this->defaultAlias.$sortBy", $sortDir);
        } else {
            $qb->orderBy("$this->defaultAlias.name", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
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
        $tag = $this->find($tagId);
        if (!$tag) {
            return [];
        }
        
        // Get addons with this tag
        $addons = $tag->getAddons();
        
        if ($addons->isEmpty()) {
            return [];
        }
        
        // Find tags used by these addons
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", 'COUNT(a.id) as frequency')
           ->from(Tag::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'a')
           ->where('a IN (:addons)')
           ->andWhere("$this->defaultAlias != :tag")
           ->setParameter('addons', $addons)
           ->setParameter('tag', $tag)
           ->groupBy("$this->defaultAlias.id")
           ->orderBy('frequency', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $relatedTags = [];
        foreach ($result as $row) {
            $relatedTag = $row[0];
            $frequency = $row['frequency'];
            
            $relatedTags[] = [
                'tag' => $relatedTag,
                'frequency' => (int)$frequency
            ];
        }
        
        return $relatedTags;
    }
    
    /**
     * Získá trendové tagy (tagy s nedávnou aktivitou)
     * 
     * @param int $days Počet dní zpět
     * @param int $limit Maximální počet tagů k vrácení
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        $date = new \DateTime();
        $date->modify("-$days days");
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", 'COUNT(a.id) as usageCount')
           ->from(Tag::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'a')
           ->where('a.created_at >= :date')
           ->setParameter('date', $date)
           ->groupBy("$this->defaultAlias.id")
           ->orderBy('usageCount', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $trendingTags = [];
        foreach ($result as $row) {
            $tag = $row[0];
            $usageCount = $row['usageCount'];
            
            $trendingTags[] = [
                'tag' => $tag,
                'usage_count' => (int)$usageCount
            ];
        }
        
        return $trendingTags;
    }
    
    /**
     * Generuje vážený tag cloud
     * 
     * @param int $limit Maximální počet tagů k zahrnutí
     * @param int|null $categoryId Volitelné ID kategorie pro filtrování
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", 'COUNT(a.id) as weight')
           ->from(Tag::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'a');
        
        if ($categoryId !== null) {
            $qb->where('a.category = :category')
               ->setParameter('category', $this->entityManager->getReference(Category::class, $categoryId));
        }
        
        $qb->groupBy("$this->defaultAlias.id")
           ->orderBy('weight', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $tags = [];
        $maxWeight = 0;
        $minWeight = PHP_INT_MAX;
        
        foreach ($result as $row) {
            $tag = $row[0];
            $weight = (int)$row['weight'];
            
            $maxWeight = max($maxWeight, $weight);
            $minWeight = min($minWeight, $weight);
            
            $tags[] = [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
                'slug' => $tag->getSlug(),
                'weight' => $weight
            ];
        }
        
        // Normalize weights to a scale of 1-10
        $weightRange = max(1, $maxWeight - $minWeight);
        
        foreach ($tags as &$tagData) {
            $normalizedWeight = 1;
            if ($weightRange > 0) {
                $normalizedWeight = 1 + floor(9 * ($tagData['weight'] - $minWeight) / $weightRange);
            }
            
            $tagData['normalized_weight'] = (int)$normalizedWeight;
        }
        
        return $tags;
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
        if (empty($categoryIds)) {
            return [];
        }
        
        $categories = array_map(
            fn($id) => $this->entityManager->getReference(Category::class, $id),
            $categoryIds
        );
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", 'COUNT(DISTINCT a.id) as addonCount')
           ->from(Tag::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'a')
           ->where('a.category IN (:categories)')
           ->setParameter('categories', $categories)
           ->groupBy("$this->defaultAlias.id")
           ->orderBy('addonCount', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $tags = [];
        foreach ($result as $row) {
            $tag = $row[0];
            $addonCount = $row['addonCount'];
            
            $tags[] = [
                'tag' => $tag,
                'addon_count' => (int)$addonCount
            ];
        }
        
        return $tags;
    }
    
    /**
     * Najde tagy přiřazené k doplňku
     * 
     * @param int $addonId ID doplňku
     * @return Collection<Tag>
     */
    public function findByAddon(int $addonId): Collection
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $tags = $addon->getTags()->toArray();
        
        return $this->createCollection($tags);
    }
}