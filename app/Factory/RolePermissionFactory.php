<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IRolePermissionFactory;
use App\Model\RolePermission;

class RolePermissionFactory implements IRolePermissionFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): RolePermission
    {
        return RolePermission::fromArray($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createRolePermission(int $roleId, int $permissionId): RolePermission
    {
        $rolePermission = new RolePermission();
        $rolePermission->role_id = $roleId;
        $rolePermission->permission_id = $permissionId;
        
        return $rolePermission;
    }
}