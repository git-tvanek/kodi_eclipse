<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Role;

/**
 * Rozhraní pro továrnu RoleFactory
 * 
 * @template-extends IBaseFactory<Role>
 */
interface IRoleFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci role z pole dat
     * 
     * @param array $data Data pro vytvoření role
     * @return Role Vytvořená instance
     */
    public function create(array $data): Role;
    
    /**
     * Aktualizuje existující entitu role
     * 
     * @param Role $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Role Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Role;
}