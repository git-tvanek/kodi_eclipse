<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ICategoryRepository;
use Nette\Database\Explorer;
use Nette\Utils\Strings;

/**
 * @extends BaseRepository<Category>
 * @implements CategoryRepositoryInterface
 */
class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'categories';
        $this->entityClass = Category::class;
    }

    /**
     * Find category by slug
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        /** @var Category|null */
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Get root categories
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection
    {
        $rows = $this->findBy(['parent_id' => null]);
        $categories = [];
        
        foreach ($rows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        return new Collection($categories);
    }

    /**
     * Get subcategories of a category
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection
    {
        $rows = $this->findBy(['parent_id' => $parentId]);
        $categories = [];
        
        foreach ($rows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        return new Collection($categories);
    }

    /**
     * Get all subcategories recursively
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection
    {
        $result = [];
        $directSubcategories = $this->findSubcategories($categoryId);
        
        foreach ($directSubcategories as $subcategory) {
            $result[] = $subcategory;
            $childSubcategories = $this->findAllSubcategoriesRecursive($subcategory->id);
            foreach ($childSubcategories as $childSubcategory) {
                $result[] = $childSubcategory;
            }
        }
        
        return new Collection($result);
    }

    /**
     * Get complete path to category (from root to the category)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection
    {
        $path = [];
        $currentCategory = $this->findById($categoryId);
        
        if (!$currentCategory) {
            return new Collection();
        }
        
        $path[] = $currentCategory;
        
        while ($currentCategory && $currentCategory->parent_id !== null) {
            $parentCategory = $this->findById($currentCategory->parent_id);
            if ($parentCategory) {
                array_unshift($path, $parentCategory);
                $currentCategory = $parentCategory;
            } else {
                break;
            }
        }
        
        return new Collection($path);
    }

    /**
     * Get category hierarchy
     * 
     * @return array
     */
    public function getHierarchy(): array
    {
        $categoryRows = $this->findAll()->fetchAll();
        $categories = [];
        
        foreach ($categoryRows as $row) {
            $categories[] = Category::fromArray($row->toArray());
        }
        
        $hierarchy = [];
        
        // First get all root categories
        foreach ($categories as $category) {
            if ($category->parent_id === null) {
                $hierarchyItem = $category->toArray();
                $hierarchyItem['subcategories'] = [];
                $hierarchy[$category->id] = $hierarchyItem;
            }
        }
        
        // Then assign subcategories
        foreach ($categories as $category) {
            if ($category->parent_id !== null && isset($hierarchy[$category->parent_id])) {
                $hierarchy[$category->parent_id]['subcategories'][] = $category->toArray();
            }
        }
        
        return array_values($hierarchy);
    }

    /**
     * Create a new category
     * 
     * @param Category $category
     * @return int
     */
    public function create(Category $category): int
    {
        // Generate slug if not provided
        if (empty($category->slug)) {
            $category->slug = Strings::webalize($category->name);
        }
        
        return $this->save($category);
    }

    /**
     * Update a category
     * 
     * @param Category $category
     * @return int
     */
    public function update(Category $category): int
    {
        // Update slug if name changed and slug is empty
        if (empty($category->slug)) {
            $category->slug = Strings::webalize($category->name);
        }
        
        return $this->save($category);
    }

    /**
     * Get most popular categories based on addon downloads
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array
    {
        $result = $this->database->query("
            SELECT c.id, c.name, c.slug, COUNT(a.id) as addon_count, SUM(a.downloads_count) as total_downloads
            FROM {$this->tableName} c
            JOIN addons a ON c.id = a.category_id
            GROUP BY c.id, c.name, c.slug
            ORDER BY total_downloads DESC
            LIMIT ?
        ", $limit);
        
        $categories = [];
        
        foreach ($result as $row) {
            $category = Category::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'parent_id' => null // We don't need this for the result
            ]);
            
            $categories[] = [
                'category' => $category,
                'addon_count' => (int)$row->addon_count,
                'total_downloads' => (int)$row->total_downloads
            ];
        }
        
        return $categories;
    }

    /**
     * Get the full hierarchy of categories with statistics
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array
    {
        // Get all categories
        $categories = $this->findAll()->fetchAll();
        
        // Get addon counts for all categories
        $addonCounts = $this->database->query("
            SELECT category_id, COUNT(*) as addon_count
            FROM addons
            GROUP BY category_id
        ")->fetchPairs('category_id', 'addon_count');
        
        // Build hierarchy with counts
        $hierarchy = [];
        $categoriesById = [];
        
        // First pass: create category objects with addon counts
        foreach ($categories as $row) {
            $category = Category::fromArray($row->toArray());
            $categoryData = [
                'category' => $category,
                'addon_count' => $addonCounts[$category->id] ?? 0,
                'total_addon_count' => $addonCounts[$category->id] ?? 0, // Will be updated in second pass
                'subcategories' => []
            ];
            
            $categoriesById[$category->id] = $categoryData;
        }
        
        // Second pass: build the tree
        foreach ($categoriesById as $id => $categoryData) {
            $category = $categoryData['category'];
            
            if ($category->parent_id === null) {
                // This is a root category
                $hierarchy[] = &$categoriesById[$id];
            } else {
                // This is a child category
                if (isset($categoriesById[$category->parent_id])) {
                    $categoriesById[$category->parent_id]['subcategories'][] = &$categoriesById[$id];
                }
            }
        }
        
        // Third pass: update total counts (including subcategories)
        $this->updateTotalCounts($hierarchy);
        
        return $hierarchy;
    }

    /**
     * Helper method to recursively update total addon counts
     * 
     * @param array &$categories
     * @return int
     */
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