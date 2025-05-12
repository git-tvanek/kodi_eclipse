<?php

declare(strict_types=1);

namespace App\Facade;

use App\Collection\Collection;
use App\Model\Category;
use App\Service\ICategoryService;

/**
 * Fasáda pro práci s kategoriemi
 */
class CategoryFacade implements IFacade
{
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /**
     * Konstruktor
     * 
     * @param ICategoryService $categoryService
     */
    public function __construct(ICategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    
    /**
     * Vytvoří novou kategorii
     * 
     * @param string $name Název kategorie
     * @param int|null $parentId ID nadřazené kategorie
     * @param string|null $slug Slug kategorie
     * @return int ID vytvořené kategorie
     */
    public function createCategory(string $name, ?int $parentId = null, ?string $slug = null): int
    {
        if ($parentId === null) {
            return $this->categoryService->createRoot($name, $slug);
        } else {
            return $this->categoryService->createSubcategory($name, $parentId, $slug);
        }
    }
    
    /**
     * Aktualizuje existující kategorii
     * 
     * @param int $categoryId ID kategorie
     * @param array $data Data kategorie
     * @return int ID aktualizované kategorie
     * @throws \Exception Pokud kategorie neexistuje
     */
    public function updateCategory(int $categoryId, array $data): int
    {
        return $this->categoryService->update($categoryId, $data);
    }
    
    /**
     * Získá kategorii podle ID
     * 
     * @param int $categoryId ID kategorie
     * @return Category|null
     */
    public function getCategory(int $categoryId): ?Category
    {
        return $this->categoryService->findById($categoryId);
    }
    
    /**
     * Získá kategorii podle slugu
     * 
     * @param string $slug Slug kategorie
     * @return Category|null
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categoryService->findBySlug($slug);
    }
    
    /**
     * Získá kořenové kategorie
     * 
     * @return Collection<Category>
     */
    public function getRootCategories(): Collection
    {
        return $this->categoryService->findRootCategories();
    }
    
    /**
     * Získá podkategorie dané kategorie
     * 
     * @param int $categoryId ID kategorie
     * @return Collection<Category>
     */
    public function getSubcategories(int $categoryId): Collection
    {
        return $this->categoryService->findSubcategories($categoryId);
    }
    
    /**
     * Získá cestu ke kategorii (breadcrumbs)
     * 
     * @param int $categoryId ID kategorie
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection
    {
        return $this->categoryService->getCategoryPath($categoryId);
    }
    
    /**
     * Získá hierarchii kategorií
     * 
     * @return array
     */
    public function getCategoryHierarchy(): array
    {
        return $this->categoryService->getHierarchy();
    }
    
    /**
     * Získá hierarchii kategorií se statistikami
     * 
     * @return array
     */
    public function getCategoryHierarchyWithStats(): array
    {
        return $this->categoryService->getHierarchyWithStats();
    }
    
    /**
     * Získá nejpopulárnější kategorie
     * 
     * @param int $limit Počet kategorií
     * @return array
     */
    public function getPopularCategories(int $limit = 10): array
    {
        return $this->categoryService->getMostPopularCategories($limit);
    }
    
    /**
     * Smaže kategorii
     * 
     * @param int $categoryId ID kategorie
     * @return bool
     */
    public function deleteCategory(int $categoryId): bool
    {
        return $this->categoryService->delete($categoryId);
    }
}