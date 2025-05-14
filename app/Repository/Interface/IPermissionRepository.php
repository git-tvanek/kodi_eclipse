<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Permission;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář oprávnění
 * 
 * @extends IBaseRepository<Permission>
 */
interface IPermissionRepository extends IBaseRepository
{
    /**
     * Najde oprávnění podle zdroje a akce
     * 
     * @param string $resource
     * @param string $action
     * @return Permission|null
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission;
    
    /**
     * Najde oprávnění podle role
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function findByRole(int $roleId): Collection;
    
    /**
     * Najde oprávnění pro uživatele
     * 
     * @param int $userId
     * @return Collection<Permission>
     */
    public function findByUser(int $userId): Collection;
    
    /**
     * Najde oprávnění s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Permission>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;

     /**
     * Zjistí, zda oprávnění existuje podle zdroje a akce
     * 
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function existsByResourceAndAction(string $resource, string $action): bool;
    
    /**
     * Vytvoří nové oprávnění
     * 
     * @param Permission $permission
     * @return int
     */
    public function create(Permission $permission): int;
    
    /**
     * Aktualizuje existující oprávnění
     * 
     * @param Permission $permission
     * @return int
     */
    public function update(Permission $permission): int;
    
    /**
     * Najde všechny dostupné zdroje
     * 
     * @return array
     */
    public function findAllResources(): array;
    
    /**
     * Najde všechny akce pro daný zdroj
     * 
     * @param string $resource
     * @return array
     */
    public function findActionsByResource(string $resource): array;
    
    /**
     * Vrátí všechny role, které mají dané oprávnění
     * 
     * @param int $permissionId
     * @return Collection<Role>
     */
    public function getRolesWithPermission(int $permissionId): Collection;
    
    /**
     * Vrátí počet rolí, které mají dané oprávnění
     * 
     * @param int $permissionId
     * @return int
     */
    public function countRolesWithPermission(int $permissionId): int;
    
    /**
     * Najde všechna oprávnění pro daný zdroj
     * 
     * @param string $resource
     * @return Collection<Permission>
     */
    public function findByResource(string $resource): Collection;
    
    /**
     * Smaže oprávnění včetně všech vazeb na role
     * 
     * @param int $permissionId
     * @return bool
     */
    public function deleteWithRoleBindings(int $permissionId): bool;
    
    /**
     * Najde oprávnění podle filtrů
     * 
     * @param array $criteria
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Permission>
     */
    public function search(
        array $criteria, 
        string $sortBy = 'name', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
    /**
     * Vrátí všechna oprávnění seskupená podle zdroje
     * 
     * @return array
     */
    public function findAllGroupedByResource(): array;
}