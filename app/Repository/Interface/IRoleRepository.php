<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Role;
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

     /**
     * Zjistí, zda role existuje podle kódu
     * 
     * @param string $code
     * @return bool
     */
    public function existsByCode(string $code): bool;
    
    /**
     * Vytvoří novou roli
     * 
     * @param Role $role
     * @return int
     */
    public function create(Role $role): int;
    
    /**
     * Aktualizuje existující roli
     * 
     * @param Role $role
     * @return int
     */
    public function update(Role $role): int;
    
    /**
     * Najde role podle priority
     * 
     * @param int $priority
     * @param string $operator
     * @return Collection<Role>
     */
    public function findByPriority(int $priority, string $operator = '='): Collection;
    
    /**
     * Najde role s vyšší nebo stejnou prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityHigherOrEqual(int $priority): Collection;
    
    /**
     * Najde role s nižší prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityLower(int $priority): Collection;
    
    /**
     * Vrátí všechna oprávnění pro roli
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function getRolePermissions(int $roleId): Collection;
    
    /**
     * Zjistí, zda role má konkrétní oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermission(int $roleId, int $permissionId): bool;
    
    /**
     * Zjistí, zda role má všechna oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAllPermissions(int $roleId, array $permissionIds): bool;
    
    /**
     * Zjistí, zda role má alespoň jedno oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAnyPermission(int $roleId, array $permissionIds): bool;
    
    /**
     * Vrátí počet uživatelů s danou rolí
     * 
     * @param int $roleId
     * @return int
     */
    public function countUsers(int $roleId): int;
    
    /**
     * Odstraní roli a všechny její oprávnění
     * 
     * @param int $roleId
     * @return bool
     */
    public function deleteWithPermissions(int $roleId): bool;
    
    /**
     * Najde role podle filtru s paginací
     * 
     * @param array $criteria
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Role>
     */
    public function search(
        array $criteria, 
        string $sortBy = 'name', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
}