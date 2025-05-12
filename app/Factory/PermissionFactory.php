<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IPermissionFactory;
use App\Model\Permission;

class PermissionFactory implements IPermissionFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Permission
    {
        return Permission::fromArray($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createPermission(string $name, string $resource, string $action, ?string $description = null): Permission
    {
        $permission = new Permission();
        $permission->name = $name;
        $permission->resource = $resource;
        $permission->action = $action;
        $permission->description = $description;
        
        return $permission;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(Permission $permission, array $data): Permission
    {
        $updatedPermission = clone $permission;
        
        if (isset($data['name'])) {
            $updatedPermission->name = $data['name'];
        }
        
        if (isset($data['resource'])) {
            $updatedPermission->resource = $data['resource'];
        }
        
        if (isset($data['action'])) {
            $updatedPermission->action = $data['action'];
        }
        
        if (isset($data['description'])) {
            $updatedPermission->description = $data['description'];
        }
        
        return $updatedPermission;
    }
}