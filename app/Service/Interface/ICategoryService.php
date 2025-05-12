<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Category;
use App\Collection\Collection;

/**
 * Rozhraní služby pro kategorie
 * 
 * @extends IBaseService<Category>
 */
interface ICategoryService extends IBaseService
{
    /**
     * Vytvoří novou kategorii
     * 
     * @param array $data
     * @return int ID vytvořené kategorie
     */
    public function create(array $data): int;
    
    /**
     * Vytvoří kořenovou kategorii
     * 
     * @param string $name
     * @param string|null $slug
     * @return int ID vytvořené kategorie
     */
    public function createRoot(string $name, ?string $slug = null): int;
    
    /**
     * Vytvoří podkategorii
     * 
     * @param string $name
     * @param int $parentId
     * @param string|null $slug
     * @return int ID vytvořené kategorie
     */
    public function createSubcategory(string $name, int $parentId, ?string $slug = null): int;
    
    /**
     * Aktualizuje existující kategorii
     * 
     * @param int $id
     * @param array $data
     * @return int ID aktualizované kategorie
     * @throws \Exception
     */
    public function update(int $id, array $data): int;
    
    /**
     * Najde kategorii podle slugu
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;
    
    /**
     * Najde kořenové kategorie
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection;
    
    /**
     * Najde podkategorie
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection;
    
    /**
     * Najde všechny podkategorie rekurzivně
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection;
    
    /**
     * Získá cestu ke kategorii (drobečková navigace)
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function getCategoryPath(int $categoryId): Collection;
    
    /**
     * Získá hierarchii kategorií
     * 
     * @return array
     */
    public function getHierarchy(): array;
    
    /**
     * Získá nejpopulárnější kategorie
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array;
    
    /**
     * Získá hierarchii kategorií se statistikami
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array;
}