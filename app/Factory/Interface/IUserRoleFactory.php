<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\UserRole;

/**
 * Rozhraní pro továrnu vazeb uživatel-role
 */
interface IUserRoleFactory
{
    /**
     * Vytvoří instanci vazby uživatel-role z dat
     * 
     * @param array $data
     * @return UserRole
     */
    public function create(array $data): UserRole;
    
    /**
     * Vytvoří novou vazbu uživatel-role
     * 
     * @param int $userId
     * @param int $roleId
     * @return UserRole
     */
    public function createUserRole(int $userId, int $roleId): UserRole;
}