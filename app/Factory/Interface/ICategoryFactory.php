<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Category;

/**
 * Rozhraní pro továrnu kategorií
 * 
 * @extends IFactory<Category>
 */
interface ICategoryFactory extends IFactory
{
    /**
     * Vytvoří novou instanci kategorie
     * 
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category;
    
    /**
     * Vytvoří kopii existující kategorie
     * 
     * @param Category $category Existující kategorie
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Category
     */
    public function createFromExisting(Category $category, array $overrideData = [], bool $createNew = true): Category;
    
    /**
     * Vytvoří kořenovou kategorii
     * 
     * @param string $name Název kategorie
     * @param string|null $slug Slug kategorie (volitelný)
     * @return Category
     */
    public function createRoot(string $name, ?string $slug = null): Category;
    
    /**
     * Vytvoří podkategorii
     * 
     * @param string $name Název kategorie
     * @param int $parentId ID nadřazené kategorie
     * @param string|null $slug Slug kategorie (volitelný)
     * @return Category
     */
    public function createSubcategory(string $name, int $parentId, ?string $slug = null): Category;
}