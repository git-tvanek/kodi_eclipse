<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Tag;

/**
 * Rozhraní pro továrnu TagFactory
 * 
 * @template-extends IBaseFactory<Tag>
 */
interface ITagFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci tagu z pole dat
     * 
     * @param array $data Data pro vytvoření tagu
     * @return Tag Vytvořená instance
     */
    public function create(array $data): Tag;
    
    /**
     * Aktualizuje existující entitu tagu
     * 
     * @param Tag $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Tag Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Tag;
}