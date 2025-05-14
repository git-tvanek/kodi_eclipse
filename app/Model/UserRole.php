<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class UserRole
{
    use SmartObject;

    public int $user_id;
    public int $role_id;

    /**
     * Create a UserRole instance from array data
     */
    public static function fromArray(array $data): self
    {
        $userRole = new self();
        
        $userRole->user_id = (int) $data['user_id'];
        $userRole->role_id = (int) $data['role_id'];
        
        return $userRole;
    }

    /**
     * Convert the UserRole instance to an array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
        ];
    }
    
    /**
     * Vytvoří novou instanci UserRole pro přidání role uživateli
     * 
     * @param int $userId
     * @param int $roleId
     * @return self
     */
    public static function create(int $userId, int $roleId): self
    {
        $userRole = new self();
        $userRole->user_id = $userId;
        $userRole->role_id = $roleId;
        return $userRole;
    }
    
    /**
     * Zkontroluje, zda záznam spojuje daného uživatele a roli
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function matches(int $userId, int $roleId): bool
    {
        return $this->user_id === $userId && $this->role_id === $roleId;
    }
}