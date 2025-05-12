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
}