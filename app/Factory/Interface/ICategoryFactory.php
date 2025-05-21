<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Category;

/**
 * Rozhraní pro továrnu CategoryFactory
 * 
 * @template-extends IBaseFactory<Category>
 */
interface ICategoryFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci kategorie z pole dat
     * 
     * @param array $data Data pro vytvoření kategorie
     * @return Category Vytvořená instance
     */
    public function create(array $data): Category;
    
    /**
     * Aktualizuje existující entitu kategorie
     * 
     * @param Category $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Category Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Category;
}