<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Permission;
use App\Repository\PermissionRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\PermissionFactory;
use App\Service\Interface;

/**
 * Implementace služby pro oprávnění
 *
 * @extends BaseService<Permission>
 * @implements IPermissionService
 */
class PermissionService extends BaseService implements IPermissionService
{
    /** @var PermissionRepository */
    private PermissionRepository $permissionRepository;
    
    /** @var PermissionFactory */
    private PermissionFactory $permissionFactory;
    
    /**
     * Konstruktor
     *
     * @param PermissionRepository $permissionRepository
     * @param PermissionFactory $permissionFactory
     */
    public function __construct(
        PermissionRepository $permissionRepository,
        PermissionFactory $permissionFactory
    ) {
        parent::__construct();
        $this->permissionRepository = $permissionRepository;
        $this->permissionFactory = $permissionFactory;
        $this->entityClass = Permission::class;
    }
    
    /**
     * Získá repozitář pro entitu
     *
     * @return PermissionRepository
     */
    protected function getRepository(): PermissionRepository
    {
        return $this->permissionRepository;
    }
    
    /**
     * Vytvoří nové oprávnění
     *
     * @param string $name Název oprávnění
     * @param string $resource Zdroj
     * @param string $action Akce
     * @param string|null $description Popis
     * @return int ID vytvořeného oprávnění
     */
    public function createPermission(string $name, string $resource, string $action, ?string $description = null): int
    {
        $permission = $this->permissionFactory->createPermission($name, $resource, $action, $description);
        return $this->save($permission);
    }
    
    /**
     * Aktualizuje existující oprávnění
     *
     * @param int $id ID oprávnění
     * @param array $data Data k aktualizaci
     * @return int ID aktualizovaného oprávnění
     */
    public function update(int $id, array $data): int
    {
        $permission = $this->findById($id);
        
        if (!$permission) {
            throw new \Exception("Oprávnění s ID $id nebylo nalezeno.");
        }
        
        $updatedPermission = $this->permissionFactory->createFromExisting($permission, $data);
        return $this->save($updatedPermission);
    }
    
    /**
     * Najde oprávnění podle zdroje a akce
     *
     * @param string $resource Zdroj
     * @param string $action Akce
     * @return Permission|null
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        return $this->permissionRepository->findByResourceAndAction($resource, $action);
    }
    
    /**
     * Najde oprávnění podle role
     *
     * @param int $roleId ID role
     * @return Collection<Permission>
     */
    public function findByRole(int $roleId): Collection
    {
        return $this->permissionRepository->findByRole($roleId);
    }
    
    /**
     * Najde oprávnění pro uživatele
     *
     * @param int $userId ID uživatele
     * @return Collection<Permission>
     */
    public function findByUser(int $userId): Collection
    {
        return $this->permissionRepository->findByUser($userId);
    }
}