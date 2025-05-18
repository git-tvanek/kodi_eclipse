<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\RolePermission;

/**
 * Rozhraní pro továrnu vazeb role-oprávnění
 */
interface IRolePermissionFactory
{
    /**
     * Vytvoří instanci vazby role-oprávnění z dat
     * 
     * @param array $data
     * @return RolePermission
     */
    public function create(array $data): RolePermission;
    
    /**
     * Vytvoří novou vazbu role-oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return RolePermission
     */
    public function createRolePermission(int $roleId, int $permissionId): RolePermission;
}