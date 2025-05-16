<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Category;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář kategorií
 * 
 * @extends BaseRepositoryInterface<Category>
 */
interface ICategoryRepository extends IBaseRepository
{
    /**
     * Najde kategorii podle slugu
     * 
     * @param string $slug
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category;
    
    /**
     * Získá kořenové kategorie
     * 
     * @return Collection<Category>
     */
    public function findRootCategories(): Collection;
    
    /**
     * Získá podkategorie kategorie
     * 
     * @param int $parentId
     * @return Collection<Category>
     */
    public function findSubcategories(int $parentId): Collection;
    
    /**
     * Získá všechny podkategorie rekurzivně
     * 
     * @param int $categoryId
     * @return Collection<Category>
     */
    public function findAllSubcategoriesRecursive(int $categoryId): Collection;
    
    /**
     * Získá kompletní cestu ke kategorii (od kořene ke kategorii)
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
     * Vytvoří novou kategorii
     * 
     * @param Category $category
     * @return int
     */
    public function create(Category $category): int;
    
    /**
     * Aktualizuje kategorii
     * 
     * @param Category $category
     * @return int
     */
    public function update(Category $category): int;
    
    /**
     * Získá nejpopulárnější kategorie podle stažení doplňků
     * 
     * @param int $limit
     * @return array
     */
    public function getMostPopularCategories(int $limit = 10): array;
    
    /**
     * Získá úplnou hierarchii kategorií se statistikami
     * 
     * @return array
     */
    public function getHierarchyWithStats(): array;
}