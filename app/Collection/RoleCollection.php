<?php
namespace App\Collection;

use App\Entity\Role;

/**
 * Typovaná kolekce pro role
 * 
 * @extends Collection<Role>
 */
class RoleCollection extends Collection
{
    /**
     * Seřadí podle názvu
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Role $a, Role $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getName(), $b->getName())
                : strcmp($b->getName(), $a->getName());
        });
    }
    
    /**
     * Seřadí podle priority
     */
    public function sortByPriority(string $direction = 'DESC'): self
    {
        return $this->sort(function(Role $a, Role $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getPriority() <=> $a->getPriority()
                : $a->getPriority() <=> $b->getPriority();
        });
    }
    
    /**
     * Filtruje podle minimální priority
     */
    public function filterByMinPriority(int $minPriority): self
    {
        return $this->filter(function(Role $role) use ($minPriority): bool {
            return $role->getPriority() >= $minPriority;
        });
    }
    
    /**
     * Najde roli podle kódu
     */
    public function findByCode(string $code): ?Role
    {
        return $this->findFirst(function(Role $role) use ($code): bool {
            return $role->getCode() === $code;
        });
    }
    
    /**
     * Získá kódy rolí jako pole
     */
    public function getCodes(): array
    {
        return $this->map(function(Role $role): string {
            return $role->getCode();
        });
    }
    
    /**
     * Získá role jako key-value pairs (code => name)
     */
    public function toCodeNamePairs(): array
    {
        return $this->reduce(function(array $pairs, Role $role): array {
            $pairs[$role->getCode()] = $role->getName();
            return $pairs;
        }, []);
    }
    
    /**
     * Filtruje role, které mají konkrétní oprávnění
     */
    public function filterWithPermission(int $permissionId): self
    {
        return $this->filter(function(Role $role) use ($permissionId): bool {
            foreach ($role->getPermissions() as $permission) {
                if ($permission->getId() === $permissionId) {
                    return true;
                }
            }
            return false;
        });
    }
    
    /**
     * Získá všechna oprávnění ze všech rolí (unikátní)
     */
    public function getAllPermissions(): PermissionCollection
    {
        $allPermissions = [];
        
        foreach ($this as $role) {
            foreach ($role->getPermissions() as $permission) {
                $allPermissions[$permission->getId()] = $permission;
            }
        }
        
        return new PermissionCollection(array_values($allPermissions));
    }
}