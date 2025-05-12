<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Model\Permission;

/**
 * Rozhraní pro továrnu oprávnění
 */
interface IPermissionFactory
{
    /**
     * Vytvoří instanci oprávnění z dat
     * 
     * @param array $data
     * @return Permission
     */
    public function create(array $data): Permission;
    
    /**
     * Vytvoří nové oprávnění
     * 
     * @param string $name
     * @param string $resource
     * @param string $action
     * @param string|null $description
     * @return Permission
     */
    public function createPermission(string $name, string $resource, string $action, ?string $description = null): Permission;
    
    /**
     * Vytvoří novou instanci z existující s aktualizovanými daty
     * 
     * @param Permission $permission
     * @param array $data
     * @return Permission
     */
    public function createFromExisting(Permission $permission, array $data): Permission;
}