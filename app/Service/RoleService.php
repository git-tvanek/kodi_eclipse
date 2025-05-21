<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\Interface\IFactoryManager;

/**
 * Implementace služby pro role
 *
 * @extends BaseService<Role>
 * @implements IRoleService
 */
class RoleService extends BaseService implements IRoleService
{
      /** @var RoleRepository */
    private RoleRepository $roleRepository;
    
    /**
     * Konstruktor
     */
    public function __construct(
        RoleRepository $roleRepository,
        IFactoryManager $factoryManager
    ) {
        parent::__construct($factoryManager);
        $this->roleRepository = $roleRepository;
        $this->entityClass = Role::class;
    }
    
    /**
     * Získá repozitář pro entitu
     *
     * @return RoleRepository
     */
    protected function getRepository(): RoleRepository
    {
        return $this->roleRepository;
    }
    
    /**
     * Vytvoří novou roli
     *
     * @param string $name Název role
     * @param string|null $code Kód role
     * @param string|null $description Popis role
     * @param int $priority Priorita role
     * @return int ID vytvořené role
     */
    public function createRole(string $name, ?string $code = null, ?string $description = null, int $priority = 0): int
    {
        // Pokud není zadán kód, vygenerujeme ho z názvu
        if ($code === null) {
            $code = \Nette\Utils\Strings::webalize($name);
        }
        
        $data = [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'priority' => $priority
        ];
        
        $role = $this->factoryManager->createRole($data);
        return $this->roleRepository->create($role);
    }
    
    /**
     * Aktualizuje existující roli
     *
     * @param int $id ID role
     * @param array $data Data k aktualizaci
     * @return int ID aktualizované role
     */
    public function update(int $id, array $data): int
    {
        $role = $this->findById($id);
        
        if (!$role) {
            throw new \Exception("Role s ID $id nebyla nalezena.");
        }
        
        $updatedRole = $this->factoryManager->getRoleFactory()->createFromExisting($role, $data, false);
        return $this->roleRepository->update($updatedRole);
    }
    
    /**
     * Najde roli podle kódu
     *
     * @param string $code Kód role
     * @return Role|null
     */
    public function findByCode(string $code): ?Role
    {
        return $this->roleRepository->findByCode($code);
    }
    
    /**
     * Získá roli s jejími oprávněními
     *
     * @param int $id ID role
     * @return array|null
     */
    public function getRoleWithPermissions(int $id): ?array
    {
        return $this->roleRepository->getRoleWithPermissions($id);
    }
    
    /**
     * Přidá roli oprávnění
     *
     * @param int $roleId ID role
     * @param int $permissionId ID oprávnění
     * @return bool Úspěch
     */
    public function addPermission(int $roleId, int $permissionId): bool
    {
        return $this->roleRepository->addRolePermission($roleId, $permissionId);
    }
    
    /**
     * Odebere roli oprávnění
     *
     * @param int $roleId ID role
     * @param int $permissionId ID oprávnění
     * @return bool Úspěch
     */
    public function removePermission(int $roleId, int $permissionId): bool
    {
        return $this->roleRepository->removeRolePermission($roleId, $permissionId);
    }
    
    /**
     * Přidá roli více oprávnění najednou
     *
     * @param int $roleId ID role
     * @param array $permissionIds ID oprávnění
     * @return bool Úspěch
     */
    public function addPermissions(int $roleId, array $permissionIds): bool
    {
        $success = true;
        
        foreach ($permissionIds as $permissionId) {
            $result = $this->addPermission($roleId, $permissionId);
            $success = $success && $result;
        }
        
        return $success;
    }
    
    /**
     * Odebere roli více oprávnění najednou
     *
     * @param int $roleId ID role
     * @param array $permissionIds ID oprávnění
     * @return bool Úspěch
     */
    public function removePermissions(int $roleId, array $permissionIds): bool
    {
        $success = true;
        
        foreach ($permissionIds as $permissionId) {
            $result = $this->removePermission($roleId, $permissionId);
            $success = $success && $result;
        }
        
        return $success;
    }
    
    /**
     * Najde role pro uživatele
     *
     * @param int $userId ID uživatele
     * @return Collection<Role>
     */
    public function findRolesByUser(int $userId): Collection
    {
        return $this->roleRepository->findRolesByUser($userId);
    }
}