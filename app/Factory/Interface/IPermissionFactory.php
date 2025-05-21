<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Permission;

/**
 * Rozhraní pro továrnu PermissionFactory
 * 
 * @template-extends IBaseFactory<Permission>
 */
interface IPermissionFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci oprávnění z pole dat
     * 
     * @param array $data Data pro vytvoření oprávnění
     * @return Permission Vytvořená instance
     */
    public function create(array $data): Permission;
    
    /**
     * Aktualizuje existující entitu oprávnění
     * 
     * @param Permission $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Permission Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Permission;
}