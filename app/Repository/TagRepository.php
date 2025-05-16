<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Tag;
use App\Entity\Addon;
use App\Entity\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ITagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Strings;

/**
 * @extends BaseDoctrineRepository<Tag>
 */
class TagRepository extends BaseRepository implements ITagRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Tag::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findBySlug(string $slug): ?Tag
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    public function findOrCreate(string $name): int
    {
        $slug = Strings::webalize($name);
        $tag = $this->findBySlug($slug);
        
        if ($tag) {
            return $tag->getId();
        }
        
        $tag = new Tag();
        $tag->setName($name);
        $tag->setSlug($slug);
        
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        
        return $tag->getId();
    }
    
    public function create(Tag $tag): int
    {
        // Generate slug if not set
        if (!$tag->getSlug()) {
            $tag->setSlug(Strings::webalize($tag->getName()));
        }
        
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        
        return $tag->getId();
    }
    
    public function update(Tag $tag): int
    {
        // Generate slug if not set
        if (!$tag->getSlug()) {
            $tag->setSlug(Strings::webalize($tag->getName()));
        }
        
        $this->entityManager->persist($tag);
        $this->entityManager->flush();
        
        return $tag->getId();
    }
    
    public function getTagsWithCounts(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'COUNT(a.id) as addonCount')
           ->from(Tag::class, 't')
           ->leftJoin('t.addons', 'a')
           ->groupBy('t.id')
           ->orderBy('t.name', 'ASC');
        
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
    
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('t');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                    $qb->andWhere('t.name LIKE :name')
                       ->setParameter('name', '%' . $value . '%');
                    break;
                
                case 'slug':
                    $qb->andWhere('t.slug LIKE :slug')
                       ->setParameter('slug', '%' . $value . '%');
                    break;
                
                case 'min_addons':
                    $qb->join('t.addons', 'a')
                       ->groupBy('t.id')
                       ->having('COUNT(a.id) >= :minAddons')
                       ->setParameter('minAddons', $value);
                    break;
                
                case 'category_id':
                    $qb->join('t.addons', 'a')
                       ->where('a.category = :category')
                       ->setParameter('category', $this->entityManager->getReference(Category::class, $value));
                    break;
                
                default:
                    if (property_exists(Tag::class, $key)) {
                        $qb->andWhere("t.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Tag::class, $sortBy)) {
            $qb->orderBy("t.$sortBy", $sortDir);
        } else {
            $qb->orderBy('t.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
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
        $qb->select('t', 'COUNT(a.id) as frequency')
           ->from(Tag::class, 't')
           ->join('t.addons', 'a')
           ->where('a IN (:addons)')
           ->andWhere('t != :tag')
           ->setParameter('addons', $addons)
           ->setParameter('tag', $tag)
           ->groupBy('t.id')
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
    
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        $date = new \DateTime();
        $date->modify("-$days days");
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'COUNT(a.id) as usageCount')
           ->from(Tag::class, 't')
           ->join('t.addons', 'a')
           ->where('a.created_at >= :date')
           ->setParameter('date', $date)
           ->groupBy('t.id')
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
    
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'COUNT(a.id) as weight')
           ->from(Tag::class, 't')
           ->join('t.addons', 'a');
        
        if ($categoryId !== null) {
            $qb->where('a.category = :category')
               ->setParameter('category', $this->entityManager->getReference(Category::class, $categoryId));
        }
        
        $qb->groupBy('t.id')
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
        $qb->select('t', 'COUNT(DISTINCT a.id) as addonCount')
           ->from(Tag::class, 't')
           ->join('t.addons', 'a')
           ->where('a.category IN (:categories)')
           ->setParameter('categories', $categories)
           ->groupBy('t.id')
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
    
    public function findByAddon(int $addonId): Collection
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $tags = $addon->getTags()->toArray();
        
        return new Collection($tags);
    }
}