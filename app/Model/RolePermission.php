<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class RolePermission
{
    use SmartObject;

    public int $role_id;
    public int $permission_id;

    /**
     * Create a RolePermission instance from array data
     */
    public static function fromArray(array $data): self
    {
        $rolePermission = new self();
        
        $rolePermission->role_id = (int) $data['role_id'];
        $rolePermission->permission_id = (int) $data['permission_id'];
        
        return $rolePermission;
    }

    /**
     * Convert the RolePermission instance to an array
     */
    public function toArray(): array
    {
        return [
            'role_id' => $this->role_id,
            'permission_id' => $this->permission_id,
        ];
    }
    
    /**
     * Vytvoří novou instanci RolePermission pro přidání oprávnění roli
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return self
     */
    public static function create(int $roleId, int $permissionId): self
    {
        $rolePermission = new self();
        $rolePermission->role_id = $roleId;
        $rolePermission->permission_id = $permissionId;
        return $rolePermission;
    }
    
    /**
     * Zkontroluje, zda záznam spojuje danou roli a oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function matches(int $roleId, int $permissionId): bool
    {
        return $this->role_id === $roleId && $this->permission_id === $permissionId;
    }
    
    /**
     * Získá identifikátor záznamu pro cache a debug účely
     * 
     * @return string
     */
    public function getIdentifier(): string
    {
        return "role:{$this->role_id}:permission:{$this->permission_id}";
    }
}