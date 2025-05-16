<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ICategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends BaseDoctrineRepository<Category>
 */
class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Category::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    public function findRootCategories(): Collection
    {
        $categories = $this->findBy(['parent' => null]);
        return new Collection($categories);
    }
    
    public function findSubcategories(int $parentId): Collection
    {
        $parent = $this->find($parentId);
        if (!$parent) {
            return new Collection([]);
        }
        
        $categories = $this->findBy(['parent' => $parent]);
        return new Collection($categories);
    }
    
    public function findAllSubcategoriesRecursive(int $categoryId): Collection
    {
        $result = [];
        $this->findSubcategoriesRecursive($categoryId, $result);
        return new Collection($result);
    }
    
    private function findSubcategoriesRecursive(int $parentId, array &$result): void
    {
        $subcategories = $this->findSubcategories($parentId);
        
        foreach ($subcategories as $subcategory) {
            $result[] = $subcategory;
            $this->findSubcategoriesRecursive($subcategory->getId(), $result);
        }
    }
    
    public function getCategoryPath(int $categoryId): Collection
    {
        $path = [];
        $category = $this->find($categoryId);
        
        while ($category) {
            array_unshift($path, $category);
            $category = $category->getParent();
        }
        
        return new Collection($path);
    }
    
    public function getHierarchy(): array
    {
        // Get all categories
        $categories = $this->findAll();
        
        // Create hierarchy
        $hierarchy = [];
        $categoriesById = [];
        
        // First pass: index categories by ID
        foreach ($categories as $category) {
            $categoryData = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'parent_id' => $category->getParent() ? $category->getParent()->getId() : null,
                'subcategories' => []
            ];
            
            $categoriesById[$category->getId()] = $categoryData;
        }
        
        // Second pass: build the tree
        foreach ($categoriesById as $id => $categoryData) {
            if ($categoryData['parent_id'] === null) {
                // This is a root category
                $hierarchy[] = $categoryData;
            } else {
                // This is a child category
                $parentId = $categoryData['parent_id'];
                if (isset($categoriesById[$parentId])) {
                    $categoriesById[$parentId]['subcategories'][] = $categoryData;
                }
            }
        }
        
        return $hierarchy;
    }
    
    public function create(Category $category): int
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        return $category->getId();
    }
    
    public function update(Category $category): int
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        return $category->getId();
    }
    
    public function getMostPopularCategories(int $limit = 10): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c', 'COUNT(a.id) as addonCount', 'SUM(a.downloads_count) as totalDownloads')
           ->from(Category::class, 'c')
           ->join('c.addons', 'a')
           ->groupBy('c.id')
           ->orderBy('totalDownloads', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $categories = [];
        foreach ($result as $row) {
            $category = $row[0];
            $addonCount = $row['addonCount'];
            $totalDownloads = $row['totalDownloads'];
            
            $categories[] = [
                'category' => $category,
                'addon_count' => (int)$addonCount,
                'total_downloads' => (int)$totalDownloads
            ];
        }
        
        return $categories;
    }
    
    public function getHierarchyWithStats(): array
    {
        // Get all categories
        $categories = $this->findAll();
        
        // Create hierarchy with statistics
        $hierarchy = [];
        $categoriesById = [];
        
        // First pass: index categories by ID and count addons
        foreach ($categories as $category) {
            $addonCount = count($category->getAddons());
            
            $categoryData = [
                'category' => $category,
                'addon_count' => $addonCount,
                'total_addon_count' => $addonCount, // Will be updated in third pass
                'subcategories' => []
            ];
            
            $categoriesById[$category->getId()] = $categoryData;
        }
        
        // Second pass: build the tree
        foreach ($categoriesById as $id => $categoryData) {
            $category = $categoryData['category'];
            $parent = $category->getParent();
            
            if (!$parent) {
                // This is a root category
                $hierarchy[] = &$categoriesById[$id];
            } else {
                // This is a child category
                $parentId = $parent->getId();
                if (isset($categoriesById[$parentId])) {
                    $categoriesById[$parentId]['subcategories'][] = &$categoriesById[$id];
                }
            }
        }
        
        // Third pass: update total counts
        $this->updateTotalCounts($hierarchy);
        
        return $hierarchy;
    }
    
    private function updateTotalCounts(array &$categories): int
    {
        $total = 0;
        
        foreach ($categories as &$categoryData) {
            $subtotal = $categoryData['addon_count'];
            
            if (!empty($categoryData['subcategories'])) {
                $subtotal += $this->updateTotalCounts($categoryData['subcategories']);
            }
            
            $categoryData['total_addon_count'] = $subtotal;
            $total += $subtotal;
        }
        
        return $total;
    }
}