<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro role
 *
 * @extends IBaseService<Role>
 */
interface IRoleService extends IBaseService
{
    /**
     * Vytvoří novou roli
     *
     * @param string $name Název role
     * @param string|null $code Kód role
     * @param string|null $description Popis role
     * @param int $priority Priorita role
     * @return int ID vytvořené role
     */
    public function createRole(string $name, ?string $code = null, ?string $description = null, int $priority = 0): int;

    /**
     * Aktualizuje existující roli
     *
     * @param int $id ID role
     * @param array $data Data k aktualizaci
     * @return int ID aktualizované role
     */
    public function update(int $id, array $data): int;

    /**
     * Najde roli podle kódu
     *
     * @param string $code Kód role
     * @return Role|null
     */
    public function findByCode(string $code): ?Role;

    /**
     * Získá roli s jejími oprávněními
     *
     * @param int $id ID role
     * @return array|null
     */
    public function getRoleWithPermissions(int $id): ?array;

    /**
     * Přidá roli oprávnění
     *
     * @param int $roleId ID role
     * @param int $permissionId ID oprávnění
     * @return bool Úspěch
     */
    public function addPermission(int $roleId, int $permissionId): bool;

    /**
     * Odebere roli oprávnění
     *
     * @param int $roleId ID role
     * @param int $permissionId ID oprávnění
     * @return bool Úspěch
     */
    public function removePermission(int $roleId, int $permissionId): bool;

    /**
     * Přidá roli více oprávnění najednou
     *
     * @param int $roleId ID role
     * @param array $permissionIds ID oprávnění
     * @return bool Úspěch
     */
    public function addPermissions(int $roleId, array $permissionIds): bool;

    /**
     * Odebere roli více oprávnění najednou
     *
     * @param int $roleId ID role
     * @param array $permissionIds ID oprávnění
     * @return bool Úspěch
     */
    public function removePermissions(int $roleId, array $permissionIds): bool;

    /**
     * Najde role pro uživatele
     *
     * @param int $userId ID uživatele
     * @return Collection<Role>
     */
    public function findRolesByUser(int $userId): Collection;
}