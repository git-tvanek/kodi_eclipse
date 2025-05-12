<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář rolí
 * 
 * @extends IBaseRepository<Role>
 */
interface IRoleRepository extends IBaseRepository
{
    /**
     * Najde roli podle kódu
     * 
     * @param string $code
     * @return Role|null
     */
    public function findByCode(string $code): ?Role;
    
    /**
     * Najde role s jejich oprávněními
     * 
     * @param int $roleId
     * @return array|null
     */
    public function getRoleWithPermissions(int $roleId): ?array;
    
    /**
     * Najde role s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Role>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Přidá roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function addRolePermission(int $roleId, int $permissionId): bool;
    
    /**
     * Odebere roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function removeRolePermission(int $roleId, int $permissionId): bool;
    
    /**
     * Získá role pro uživatele
     * 
     * @param int $userId
     * @return Collection<Role>
     */
    public function findRolesByUser(int $userId): Collection;
}