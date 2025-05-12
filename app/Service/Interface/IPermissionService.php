<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Permission;
use App\Collection\Collection;

/**
 * Rozhraní služby pro oprávnění
 *
 * @extends IBaseService<Permission>
 */
interface IPermissionService extends IBaseService
{
    /**
     * Vytvoří nové oprávnění
     *
     * @param string $name Název oprávnění
     * @param string $resource Zdroj
     * @param string $action Akce
     * @param string|null $description Popis
     * @return int ID vytvořeného oprávnění
     */
    public function createPermission(string $name, string $resource, string $action, ?string $description = null): int;

    /**
     * Aktualizuje existující oprávnění
     *
     * @param int $id ID oprávnění
     * @param array $data Data k aktualizaci
     * @return int ID aktualizovaného oprávnění
     */
    public function update(int $id, array $data): int;

    /**
     * Najde oprávnění podle zdroje a akce
     *
     * @param string $resource Zdroj
     * @param string $action Akce
     * @return Permission|null
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission;

    /**
     * Najde oprávnění podle role
     *
     * @param int $roleId ID role
     * @return Collection<Permission>
     */
    public function findByRole(int $roleId): Collection;

    /**
     * Najde oprávnění pro uživatele
     *
     * @param int $userId ID uživatele
     * @return Collection<Permission>
     */
    public function findByUser(int $userId): Collection;
}