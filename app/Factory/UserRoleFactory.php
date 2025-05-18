<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IUserRoleFactory;
use App\Entity\UserRole;

class UserRoleFactory implements IUserRoleFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): UserRole
    {
        return UserRole::fromArray($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createUserRole(int $userId, int $roleId): UserRole
    {
        $userRole = new UserRole();
        $userRole->user_id = $userId;
        $userRole->role_id = $roleId;
        
        return $userRole;
    }
}