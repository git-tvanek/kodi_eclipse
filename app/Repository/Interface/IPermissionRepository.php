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
}