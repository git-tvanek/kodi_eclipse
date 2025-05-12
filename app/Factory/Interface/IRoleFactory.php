<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Model\Role;

/**
 * Rozhraní pro továrnu rolí
 */
interface IRoleFactory
{
    /**
     * Vytvoří instanci role z dat
     * 
     * @param array $data
     * @return Role
     */
    public function create(array $data): Role;
    
    /**
     * Vytvoří novou roli z názvu
     * 
     * @param string $name
     * @param string|null $code
     * @param string|null $description
     * @param int $priority
     * @return Role
     */
    public function createFromName(string $name, ?string $code = null, ?string $description = null, int $priority = 0): Role;
    
    /**
     * Vytvoří novou instanci z existující s aktualizovanými daty
     * 
     * @param Role $role
     * @param array $data
     * @return Role
     */
    public function createFromExisting(Role $role, array $data): Role;
}