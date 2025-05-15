<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Collection\Collection;
use App\Collection\AddonCollection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAddonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Repozitář pro práci s doplňky
 * 
 * @extends BaseDoctrineRepository<Addon>
 */
class AddonRepository extends BaseDoctrineRepository implements IAddonRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Addon::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new AddonCollection($entities);
    }
    
    // -------------------------------------------------------------------------
    // ZÁKLADNÍ VYHLEDÁVACÍ METODY
    // -------------------------------------------------------------------------
    
    /**
     * Najde doplněk podle slugu
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    /**
     * Najde doplňky podle autora
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.author = :author')
            ->setParameter('author', $this->entityManager->getReference(Author::class, $authorId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    // -------------------------------------------------------------------------
    // METODY SOUVISEJÍCÍ S KATEGORIEMI
    // -------------------------------------------------------------------------
    
    /**
     * Najde doplňky v konkrétní kategorii
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.category = :category')
            ->setParameter('category', $this->entityManager->getReference(Category::class, $categoryId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Najde doplňky v kategorii a všech jejích podkategoriích
     */
    public function findByCategoryRecursive(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Get all subcategory IDs
        $categoryIds = [$categoryId];
        $this->findAllSubcategoryIds($categoryId, $categoryIds);
        
        // Find addons in all categories
        $qb = $this->createQueryBuilder('a')
            ->where('a.category IN (:categories)')
            ->setParameter('categories', array_map(
                fn($id) => $this->entityManager->getReference(Category::class, $id),
                $categoryIds
            ))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pomocná metoda pro rekurzivní hledání ID všech podkategorií
     */
    private function findAllSubcategoryIds(int $parentId, array &$categoryIds): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $subcategories = $qb->select('c.id')
            ->from(Category::class, 'c')
            ->where('c.parent = :parent')
            ->setParameter('parent', $this->entityManager->getReference(Category::class, $parentId))
            ->getQuery()
            ->getResult();
        
        foreach ($subcategories as $subcategory) {
            $subId = $subcategory['id'];
            $categoryIds[] = $subId;
            $this->findAllSubcategoryIds($subId, $categoryIds);
        }
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO STATISTIKY A DOPORUČENÍ
    // -------------------------------------------------------------------------
    
    /**
     * Najde nejstahovanější doplňky
     */
    public function findPopular(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.downloads_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde nejlépe hodnocené doplňky
     */
    public function findTopRated(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde nejnovější doplňky
     */
    public function findNewest(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde podobné doplňky k zadanému doplňku
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        $addon = $this->find($addonId);
        if (!$addon) {
            return new AddonCollection([]);
        }
        
        $category = $addon->getCategory();
        $tags = $addon->getTags();
        
        $qb = $this->createQueryBuilder('a')
            ->where('a.id != :addonId')
            ->setParameter('addonId', $addonId);
        
        // If addon has tags, use them for finding similar addons
        if (count($tags) > 0) {
            $qb->join('a.tags', 't')
               ->andWhere('t IN (:tags)')
               ->andWhere('a.category = :category')
               ->setParameter('tags', $tags)
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->setMaxResults($limit);
        } else {
            // Otherwise just use category
            $qb->andWhere('a.category = :category')
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->setMaxResults($limit);
        }
        
        $similarAddons = $qb->getQuery()->getResult();
        return new AddonCollection($similarAddons);
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO VYHLEDÁVÁNÍ A FILTROVÁNÍ
    // -------------------------------------------------------------------------
    
    /**
     * Vyhledá doplňky podle klíčového slova
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.name LIKE :query')
            ->orWhere('a.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pokročilé vyhledávání s možností filtrování
     */
    public function advancedSearch(string $query, array $fields = ['name', 'description'], array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a');
        
        // Add search query if not empty
        if (!empty($query)) {
            $orExpressions = $qb->expr()->orX();
            foreach ($fields as $field) {
                $orExpressions->add($qb->expr()->like("a.$field", ":query"));
            }
            $qb->andWhere($orExpressions)
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Apply filters
        $this->applyFilters($qb, $filters);
        
        // Apply sorting
        if (!empty($query)) {
            // For text search, sort by name
            $qb->orderBy('a.name', 'ASC');
        } else if (isset($filters['sort_by'])) {
            $qb->orderBy('a.' . $filters['sort_by'], $filters['sort_dir'] ?? 'ASC');
        } else {
            // Default sorting by downloads
            $qb->orderBy('a.downloads_count', 'DESC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pomocná metoda pro aplikaci filtrů na dotaz
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '' || $key === 'sort_by' || $key === 'sort_dir') {
                continue;
            }
            
            switch ($key) {
                case 'category_ids':
                    if (is_array($value) && !empty($value)) {
                        $categories = array_map(
                            fn($id) => $this->entityManager->getReference(Category::class, $id),
                            $value
                        );
                        $qb->andWhere('a.category IN (:categories)')
                           ->setParameter('categories', $categories);
                    } else if (!is_array($value)) {
                        $qb->andWhere('a.category = :category')
                           ->setParameter('category', $this->entityManager->getReference(Category::class, $value));
                    }
                    break;
                
                case 'author_ids':
                    if (is_array($value) && !empty($value)) {
                        $authors = array_map(
                            fn($id) => $this->entityManager->getReference(Author::class, $id),
                            $value
                        );
                        $qb->andWhere('a.author IN (:authors)')
                           ->setParameter('authors', $authors);
                    } else if (!is_array($value)) {
                        $qb->andWhere('a.author = :author')
                           ->setParameter('author', $this->entityManager->getReference(Author::class, $value));
                    }
                    break;
                
                case 'tag_ids':
                    if (is_array($value) && !empty($value)) {
                        $tags = array_map(
                            fn($id) => $this->entityManager->getReference(Tag::class, $id),
                            $value
                        );
                        $qb->join('a.tags', 'tag')
                           ->andWhere('tag IN (:tags)')
                           ->setParameter('tags', $tags);
                    }
                    break;
                
                case 'min_rating':
                    $qb->andWhere('a.rating >= :minRating')
                       ->setParameter('minRating', $value);
                    break;
                
                case 'max_rating':
                    $qb->andWhere('a.rating <= :maxRating')
                       ->setParameter('maxRating', $value);
                    break;
                
                case 'min_downloads':
                    $qb->andWhere('a.downloads_count >= :minDownloads')
                       ->setParameter('minDownloads', $value);
                    break;
                
                case 'max_downloads':
                    $qb->andWhere('a.downloads_count <= :maxDownloads')
                       ->setParameter('maxDownloads', $value);
                    break;
                
                case 'kodi_version':
                    $qb->andWhere('(a.kodi_version_min IS NULL OR a.kodi_version_min <= :kodiVersion)')
                       ->andWhere('(a.kodi_version_max IS NULL OR a.kodi_version_max >= :kodiVersion)')
                       ->setParameter('kodiVersion', $value);
                    break;
                
                default:
                    // For other standard fields
                    if (property_exists(Addon::class, $key)) {
                        $qb->andWhere("a.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO AKTUALIZACI A MANIPULACI S DATY
    // -------------------------------------------------------------------------
    
    /**
     * Zvýší počet stažení doplňku
     */
    public function incrementDownloadCount(int $id): int
    {
        $addon = $this->find($id);
        if ($addon) {
            $addon->incrementDownloadsCount();
            $this->entityManager->flush();
            return 1;
        }
        return 0;
    }
    
    /**
     * Aktualizuje hodnocení doplňku
     */
    public function updateRating(int $id): void
    {
        $addon = $this->find($id);
        if (!$addon) {
            return;
        }
        
        $qb = $this->entityManager->createQueryBuilder();
        $avgRating = $qb->select('AVG(r.rating)')
            ->from('App\Entity\AddonReview', 'r')
            ->where('r.addon = :addon')
            ->setParameter('addon', $addon)
            ->getQuery()
            ->getSingleScalarResult();
        
        $addon->setRating($avgRating ?: 0);
        $this->entityManager->flush();
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO PRÁCI S RELAČNÍMI DATY
    // -------------------------------------------------------------------------
    
    /**
     * Vytvoří nový doplněk včetně souvisejících entit
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Add screenshots
            foreach ($screenshots as $screenshot) {
                $screenshot->setAddon($addon);
                $addon->addScreenshot($screenshot);
                $this->entityManager->persist($screenshot);
            }
            
            // Add tags
            foreach ($tagIds as $tagId) {
                $tag = $this->entityManager->getReference(Tag::class, $tagId);
                $addon->addTag($tag);
            }
            
            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $addon->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Aktualizuje doplněk včetně souvisejících entit
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Remove existing screenshots
            foreach ($addon->getScreenshots() as $screenshot) {
                $addon->removeScreenshot($screenshot);
                $this->entityManager->remove($screenshot);
            }
            
            // Add new screenshots
            foreach ($screenshots as $screenshot) {
                $screenshot->setAddon($addon);
                $addon->addScreenshot($screenshot);
                $this->entityManager->persist($screenshot);
            }
            
            // Remove existing tags
            foreach ($addon->getTags() as $tag) {
                $addon->removeTag($tag);
            }
            
            // Add new tags
            foreach ($tagIds as $tagId) {
                $tag = $this->entityManager->getReference(Tag::class, $tagId);
                $addon->addTag($tag);
            }
            
            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $addon->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Načte doplněk včetně všech souvisejících entit
     */
    public function getWithRelated(int $id): ?array
    {
        $addon = $this->find($id);
        
        if (!$addon) {
            return null;
        }
        
        // Eager load associated entities
        $author = $addon->getAuthor();
        $category = $addon->getCategory();
        $screenshots = $addon->getScreenshots()->toArray();
        $tags = $addon->getTags()->toArray();
        
        // Get reviews
        $reviews = $this->entityManager->getRepository('App\Entity\AddonReview')
            ->findBy(['addon' => $addon], ['created_at' => 'DESC']);
        
        return [
            'addon' => $addon,
            'author' => $author,
            'category' => $category,
            'screenshots' => $screenshots,
            'tags' => $tags,
            'reviews' => $reviews
        ];
    }
}