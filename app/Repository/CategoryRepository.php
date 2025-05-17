<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ICategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci s kategoriemi
 * 
 * @extends BaseRepository<Category>
 */
class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    protected string $defaultAlias = 'c';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Category::class);
    }
    
    /**
     * Vytvoří typovanou kolekci kategorií
     * 
     * @param array<Category> $entities
     * @return Collection<Category>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde kategorii podle slugu
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    /**
     * Získá kořenové kategorie
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection
    {
        $categories = $this->findBy(['parent' => null]);
        return $this->createCollection($categories);
    }
    
    /**
     * Získá podkategorie kategorie
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection
    {
        $parent = $this->find($parentId);
        if (!$parent) {
            return $this->createCollection([]);
        }
        
        $categories = $this->findBy(['parent' => $parent]);
        return $this->createCollection($categories);
    }
    
    /**
     * Získá všechny podkategorie rekurzivně
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection
    {
        $result = [];
        $this->findSubcategoriesRecursive($categoryId, $result);
        return $this->createCollection($result);
    }
    
    /**
     * Pomocná metoda pro rekurzivní hledání všech podkategorií
     * 
     * @param int $parentId
     * @param array &$result Reference na pole výsledků
     */
    private function findSubcategoriesRecursive(int $parentId, array &$result): void
    {
        $subcategories = $this->findSubcategories($parentId);
        
        foreach ($subcategories as $subcategory) {
            $result[] = $subcategory;
            $this->findSubcategoriesRecursive($subcategory->getId(), $result);
        }
    }
    
    /**
     * Získá kompletní cestu ke kategorii (od kořene ke kategorii)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection
    {
        $path = [];
        $category = $this->find($categoryId);
        
        while ($category) {
            array_unshift($path, $category);
            $category = $category->getParent();
        }
        
        return $this->createCollection($path);
    }
    
    /**
     * Získá hierarchii kategorií
     * 
     * @return array
     */
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
                $hierarchy[] = &$categoriesById[$id];
            } else {
                // This is a child category
                $parentId = $categoryData['parent_id'];
                if (isset($categoriesById[$parentId])) {
                    $categoriesById[$parentId]['subcategories'][] = &$categoriesById[$id];
                }
            }
        }
        
        return $hierarchy;
    }
    
    /**
     * Vytvoří novou kategorii
     * 
     * @param Category $category
     * @return int ID vytvořené kategorie
     */
    public function create(Category $category): int
    {
        return $this->save($category);
    }
    
    /**
     * Aktualizuje kategorii
     * 
     * @param Category $category
     * @return int ID aktualizované kategorie
     */
    public function update(Category $category): int
    {
        return $this->updateEntity($category);
    }
    
    /**
     * Získá nejpopulárnější kategorie podle stažení doplňků
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias", 'COUNT(a.id) as addonCount', 'SUM(a.downloads_count) as totalDownloads')
           ->from(Category::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'a')
           ->groupBy("$this->defaultAlias.id")
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
    
    /**
     * Získá úplnou hierarchii kategorií se statistikami
     * 
     * @return array
     */
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
        foreach ($categoriesById as $id => &$categoryData) {
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
    
    /**
     * Aktualizuje počty doplňků v hierarchii
     * 
     * @param array &$categories
     * @return int Počet doplňků v této větvi
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