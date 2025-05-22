<?php
namespace App\Collection;

use App\Entity\Permission;

/**
 * Typovaná kolekce pro oprávnění
 * 
 * @extends Collection<Permission>
 */
class PermissionCollection extends Collection
{
    /**
     * Seřadí podle názvu
     */
    public function sortByName(string $direction = 'ASC'): self
    {
        return $this->sort(function(Permission $a, Permission $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getName(), $b->getName())
                : strcmp($b->getName(), $a->getName());
        });
    }
    
    /**
     * Filtruje podle zdroje (resource)
     */
    public function filterByResource(string $resource): self
    {
        return $this->filter(function(Permission $permission) use ($resource): bool {
            return $permission->getResource() === $resource;
        });
    }
    
    /**
     * Filtruje podle akce
     */
    public function filterByAction(string $action): self
    {
        return $this->filter(function(Permission $permission) use ($action): bool {
            return $permission->getAction() === $action;
        });
    }
    
    /**
     * Seskupí podle zdrojů
     */
    public function groupByResource(): array
    {
        return $this->groupBy('resource');
    }
    
    /**
     * Seskupí podle akcí
     */
    public function groupByAction(): array
    {
        return $this->groupBy('action');
    }
    
    /**
     * Získá unikátní zdroje
     */
    public function getUniqueResources(): array
    {
        return array_unique($this->map(function(Permission $permission): string {
            return $permission->getResource();
        }));
    }
    
    /**
     * Získá unikátní akce
     */
    public function getUniqueActions(): array
    {
        return array_unique($this->map(function(Permission $permission): string {
            return $permission->getAction();
        }));
    }
    
    /**
     * Vytvoří asociativní pole resource:action => Permission
     */
    public function toResourceActionMap(): array
    {
        $map = [];
        
        foreach ($this as $permission) {
            $key = $permission->getResource() . ':' . $permission->getAction();
            $map[$key] = $permission;
        }
        
        return $map;
    }
    
    /**
     * Najde oprávnění podle resource a action
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        return $this->findFirst(function(Permission $permission) use ($resource, $action): bool {
            return $permission->getResource() === $resource && $permission->getAction() === $action;
        });
    }
}