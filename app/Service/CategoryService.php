<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Collection\Collection;
use App\Factory\Interface\IFactoryManager;

/**
 * Implementace služby pro kategorie
 * 
 * @extends BaseService<Category>
 * @implements ICategoryService
 */
class CategoryService extends BaseService implements ICategoryService
{
     /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;
    
    /**
     * Konstruktor
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        IFactoryManager $factoryManager
    ) {
        parent::__construct($factoryManager);
        $this->categoryRepository = $categoryRepository;
        $this->entityClass = Category::class;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return CategoryRepository
     */
    protected function getRepository(): CategoryRepository
    {
        return $this->categoryRepository;
    }
    
    /**
     * Vytvoří novou kategorii
     * 
     * @param array $data
     * @return int ID vytvořené kategorie
     */
    public function create(array $data): int
    {
        $category = $this->factoryManager->createCategory($data);
        return $this->categoryRepository->create($category);
    }
    
    /**
     * Vytvoří kořenovou kategorii
     * 
     * @param string $name
     * @param string|null $slug
     * @return int ID vytvořené kategorie
     */
    public function createRoot(string $name, ?string $slug = null): int
    {
        $data = [
            'name' => $name,
            'slug' => $slug ?? \Nette\Utils\Strings::webalize($name),
            'parent' => null
        ];
        
        return $this->create($data);
    }
    
    /**
     * Vytvoří podkategorii
     * 
     * @param string $name
     * @param int $parentId
     * @param string|null $slug
     * @return int ID vytvořené kategorie
     */
    public function createSubcategory(string $name, int $parentId, ?string $slug = null): int
    {
        $data = [
            'name' => $name,
            'slug' => $slug ?? \Nette\Utils\Strings::webalize($name),
            'parent_id' => $parentId
        ];
        
        return $this->create($data);
    }
    
    /**
     * Aktualizuje existující kategorii
     * 
     * @param int $id
     * @param array $data
     * @return int ID aktualizované kategorie
     * @throws \Exception
     */
    public function update(int $id, array $data): int
    {
        $category = $this->findById($id);
        
        if (!$category) {
            throw new \Exception("Kategorie s ID {$id} nebyla nalezena.");
        }
        
        $updatedCategory = $this->factoryManager->getCategoryFactory()->createFromExisting($category, $data, false);
        return $this->categoryRepository->update($updatedCategory);
    }
    
    /**
     * Najde kategorii podle slugu
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }
    
    /**
     * Najde kořenové kategorie
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection
    {
        return $this->categoryRepository->findRootCategories();
    }
    
    /**
     * Najde podkategorie
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection
    {
        return $this->categoryRepository->findSubcategories($parentId);
    }
    
    /**
     * Najde všechny podkategorie rekurzivně
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection
    {
        return $this->categoryRepository->findAllSubcategoriesRecursive($categoryId);
    }
    
    /**
     * Získá cestu ke kategorii (drobečková navigace)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection
    {
        return $this->categoryRepository->getCategoryPath($categoryId);
    }
    
    /**
     * Získá hierarchii kategorií
     * 
     * @return array
     */
    public function getHierarchy(): array
    {
        return $this->categoryRepository->getHierarchy();
    }
    
    /**
     * Získá nejpopulárnější kategorie
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array
    {
        return $this->categoryRepository->getMostPopularCategories($limit);
    }
    
    /**
     * Získá hierarchii kategorií se statistikami
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array
    {
        return $this->categoryRepository->getHierarchyWithStats();
    }
}