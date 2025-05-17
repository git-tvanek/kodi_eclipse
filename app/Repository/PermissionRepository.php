<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Permission;
use App\Entity\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IPermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Repozitář pro práci s oprávněními
 * 
 * @extends BaseRepository<Permission>
 */
class PermissionRepository extends BaseRepository implements IPermissionRepository
{
    protected string $defaultAlias = 'p';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Permission::class);
    }
    
    /**
     * Vytvoří typovanou kolekci oprávnění
     * 
     * @param array<Permission> $entities
     * @return Collection<Permission>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde oprávnění podle zdroje a akce
     * 
     * @param string $resource
     * @param string $action
     * @return Permission|null
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        return $this->findOneBy([
            'resource' => $resource,
            'action' => $action
        ]);
    }
    
    /**
     * Najde oprávnění podle role
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function findByRole(int $roleId): Collection
    {
        $role = $this->entityManager->getReference(Role::class, $roleId);
        $permissions = $role->getPermissions()->toArray();
        
        return $this->createCollection($permissions);
    }
    
    /**
     * Najde oprávnění pro uživatele
     * 
     * @param int $userId
     * @return Collection<Permission>
     */
    public function findByUser(int $userId): Collection
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("DISTINCT $this->defaultAlias")
           ->from(Permission::class, $this->defaultAlias)
           ->join("$this->defaultAlias.roles", 'r')
           ->join('r.users', 'u')
           ->where('u.id = :userId')
           ->setParameter('userId', $userId);
        
        $permissions = $qb->getQuery()->getResult();
        
        return $this->createCollection($permissions);
    }
    
    /**
     * Vyhledá oprávnění podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Permission> Stránkovaná kolekce oprávnění
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        // Apply standard filters
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        // Apply custom filters specific to permissions
        if (isset($filters['role_id']) && $filters['role_id'] !== null) {
            $qb->join("$this->defaultAlias.roles", 'r')
               ->andWhere('r.id = :roleId')
               ->setParameter('roleId', $filters['role_id']);
        }
        
        if (isset($filters['user_id']) && $filters['user_id'] !== null) {
            $qb->join("$this->defaultAlias.roles", 'r')
               ->join('r.users', 'u')
               ->andWhere('u.id = :userId')
               ->setParameter('userId', $filters['user_id']);
        }
        
        // Apply sorting
        if ($this->hasProperty($sortBy)) {
            $qb->orderBy("$this->defaultAlias.$sortBy", $sortDir);
        } else {
            $qb->orderBy("$this->defaultAlias.name", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Zjistí, zda oprávnění existuje podle zdroje a akce
     * 
     * @param string $resource
     * @param string $action
     * @return bool
     */
    public function existsByResourceAndAction(string $resource, string $action): bool
    {
        return $this->findByResourceAndAction($resource, $action) !== null;
    }
    
    /**
     * Vytvoří nové oprávnění
     * 
     * @param Permission $permission
     * @return int
     */
    public function create(Permission $permission): int
    {
        return $this->transaction(function() use ($permission) {
            // Check uniqueness of resource:action
            if ($this->existsByResourceAndAction($permission->getResource(), $permission->getAction())) {
                throw new \Exception("Permission for '{$permission->getResource()}:{$permission->getAction()}' already exists.");
            }
            
            $this->entityManager->persist($permission);
            $this->entityManager->flush();
            
            return $permission->getId();
        });
    }
    
    /**
     * Aktualizuje existující oprávnění
     * 
     * @param Permission $permission
     * @return int
     */
    public function update(Permission $permission): int
    {
        return $this->transaction(function() use ($permission) {
            // Check uniqueness of resource:action if changed
            $originalPermission = $this->find($permission->getId());
            
            if ($originalPermission &&
                ($originalPermission->getResource() !== $permission->getResource() ||
                 $originalPermission->getAction() !== $permission->getAction()) &&
                $this->existsByResourceAndAction($permission->getResource(), $permission->getAction())) {
                throw new \Exception("Permission for '{$permission->getResource()}:{$permission->getAction()}' already exists.");
            }
            
            $this->entityManager->persist($permission);
            $this->entityManager->flush();
            
            return $permission->getId();
        });
    }
    
    /**
     * Najde všechny dostupné zdroje
     * 
     * @return array
     */
    public function findAllResources(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("DISTINCT $this->defaultAlias.resource")
           ->from(Permission::class, $this->defaultAlias)
           ->orderBy("$this->defaultAlias.resource", 'ASC');
        
        $result = $qb->getQuery()->getScalarResult();
        
        return array_column($result, 'resource');
    }
    
    /**
     * Najde všechny akce pro daný zdroj
     * 
     * @param string $resource
     * @return array
     */
    public function findActionsByResource(string $resource): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias.action")
           ->from(Permission::class, $this->defaultAlias)
           ->where("$this->defaultAlias.resource = :resource")
           ->setParameter('resource', $resource)
           ->orderBy("$this->defaultAlias.action", 'ASC');
        
        $result = $qb->getQuery()->getScalarResult();
        
        return array_column($result, 'action');
    }
    
    /**
     * Vrátí všechny role, které mají dané oprávnění
     * 
     * @param int $permissionId
     * @return Collection<Role>
     */
    public function getRolesWithPermission(int $permissionId): Collection
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return $this->createCollection([]);
        }
        
        $roles = $permission->getRoles()->toArray();
        
        return $this->createCollection($roles);
    }
    
    /**
     * Vrátí počet rolí, které mají dané oprávnění
     * 
     * @param int $permissionId
     * @return int
     */
    public function countRolesWithPermission(int $permissionId): int
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return 0;
        }
        
        return $permission->getRoles()->count();
    }
    
    /**
     * Najde všechna oprávnění pro daný zdroj
     * 
     * @param string $resource
     * @return Collection<Permission>
     */
    public function findByResource(string $resource): Collection
    {
        $permissions = $this->findBy(['resource' => $resource]);
        return $this->createCollection($permissions);
    }
    
    /**
     * Smaže oprávnění včetně všech vazeb na role
     * 
     * @param int $permissionId
     * @return bool
     */
    public function deleteWithRoleBindings(int $permissionId): bool
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return false;
        }
        
        return $this->transaction(function() use ($permission) {
            // Remove from all roles
            foreach ($permission->getRoles() as $role) {
                $role->removePermission($permission);
            }
            
            // Delete the permission
            $this->entityManager->remove($permission);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
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
    ): PaginatedCollection
    {
        return $this->findWithFilters($criteria, $sortBy, $sortDir, $page, $itemsPerPage);
    }
    
    /**
     * Vrátí všechna oprávnění seskupená podle zdroje
     * 
     * @return array
     */
    public function findAllGroupedByResource(): array
    {
        $result = [];
        
        // Get all resources
        $resources = $this->findAllResources();
        
        foreach ($resources as $resource) {
            $permissions = $this->findByResource($resource)->toArray();
            $result[$resource] = $permissions;
        }
        
        return $result;
    }
}