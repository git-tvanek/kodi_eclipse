<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Author;

/**
 * Rozhraní pro továrnu AuthorFactory
 * 
 * @template-extends IBaseFactory<Author>
 */
interface IAuthorFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci autora z pole dat
     * 
     * @param array $data Data pro vytvoření autora
     * @return Author Vytvořená instance
     */
    public function create(array $data): Author;
    
    /**
     * Aktualizuje existující entitu autora
     * 
     * @param Author $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Author Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Author;
}