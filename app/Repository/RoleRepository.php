<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use App\Entity\Permission;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IRoleRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci s rolemi
 * 
 * @extends BaseRepository<Role>
 */
class RoleRepository extends BaseRepository implements IRoleRepository
{
    protected string $defaultAlias = 'r';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Role::class);
    }
    
    /**
     * Vytvoří typovanou kolekci rolí
     * 
     * @param array<Role> $entities
     * @return Collection<Role>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde roli podle kódu
     * 
     * @param string $code
     * @return Role|null
     */
    public function findByCode(string $code): ?Role
    {
        return $this->findOneBy(['code' => $code]);
    }
    
    /**
     * Najde role s jejich oprávněními
     * 
     * @param int $roleId
     * @return array|null
     */
    public function getRoleWithPermissions(int $roleId): ?array
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return null;
        }
        
        return [
            'role' => $role,
            'permissions' => $this->createCollection($role->getPermissions()->toArray())
        ];
    }
    
    /**
     * Vyhledá role podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Role> Stránkovaná kolekce rolí
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        // Apply standard filters
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        // Apply custom filters specific to roles
        if (isset($filters['permission_id']) && $filters['permission_id'] !== null) {
            $qb->join("$this->defaultAlias.permissions", 'p')
               ->andWhere('p.id = :permissionId')
               ->setParameter('permissionId', $filters['permission_id']);
        }
        
        if (isset($filters['min_priority']) && $filters['min_priority'] !== null) {
            $qb->andWhere("$this->defaultAlias.priority >= :minPriority")
               ->setParameter('minPriority', $filters['min_priority']);
        }
        
        if (isset($filters['max_priority']) && $filters['max_priority'] !== null) {
            $qb->andWhere("$this->defaultAlias.priority <= :maxPriority")
               ->setParameter('maxPriority', $filters['max_priority']);
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
     * Přidá roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function addRolePermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        return $this->transaction(function() use ($role, $permission) {
            // Check if already exists
            if ($role->getPermissions()->contains($permission)) {
                return true;
            }
            
            $role->addPermission($permission);
            $this->updateTimestamps($role, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Odebere roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function removeRolePermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        return $this->transaction(function() use ($role, $permission) {
            if (!$role->getPermissions()->contains($permission)) {
                return true;
            }
            
            $role->removePermission($permission);
            $this->updateTimestamps($role, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Získá role pro uživatele
     * 
     * @param int $userId
     * @return Collection<Role>
     */
    public function findRolesByUser(int $userId): Collection
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select($this->defaultAlias)
           ->from(Role::class, $this->defaultAlias)
           ->join("$this->defaultAlias.users", 'u')
           ->where('u.id = :userId')
           ->setParameter('userId', $userId)
           ->orderBy("$this->defaultAlias.priority", 'DESC');
        
        $roles = $qb->getQuery()->getResult();
        
        return $this->createCollection($roles);
    }
    
    /**
     * Zjistí, zda role existuje podle kódu
     * 
     * @param string $code
     * @return bool
     */
    public function existsByCode(string $code): bool
    {
        return $this->findByCode($code) !== null;
    }
    
    /**
     * Vytvoří novou roli
     * 
     * @param Role $role
     * @return int
     */
    public function create(Role $role): int
    {
        return $this->transaction(function() use ($role) {
            // Check uniqueness of code
            if ($this->existsByCode($role->getCode())) {
                throw new \Exception("Role with code '{$role->getCode()}' already exists.");
            }
            
            $this->updateTimestamps($role);
            $this->entityManager->persist($role);
            $this->entityManager->flush();
            
            return $role->getId();
        });
    }
    
    /**
     * Aktualizuje existující roli
     * 
     * @param Role $role
     * @return int
     */
    public function update(Role $role): int
    {
        return $this->transaction(function() use ($role) {
            // Check uniqueness of code if changed
            $originalRole = $this->find($role->getId());
            
            if ($originalRole &&
                $originalRole->getCode() !== $role->getCode() &&
                $this->existsByCode($role->getCode())) {
                throw new \Exception("Role with code '{$role->getCode()}' already exists.");
            }
            
            $this->updateTimestamps($role, false);
            $this->entityManager->persist($role);
            $this->entityManager->flush();
            
            return $role->getId();
        });
    }
    
    /**
     * Najde role podle priority
     * 
     * @param int $priority
     * @param string $operator
     * @return Collection<Role>
     */
    public function findByPriority(int $priority, string $operator = '='): Collection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        switch ($operator) {
            case '>':
                $qb->where("$this->defaultAlias.priority > :priority");
                break;
            case '>=':
                $qb->where("$this->defaultAlias.priority >= :priority");
                break;
            case '<':
                $qb->where("$this->defaultAlias.priority < :priority");
                break;
            case '<=':
                $qb->where("$this->defaultAlias.priority <= :priority");
                break;
            case '=':
            default:
                $qb->where("$this->defaultAlias.priority = :priority");
                break;
        }
        
        $qb->setParameter('priority', $priority)
           ->orderBy("$this->defaultAlias.priority", 'DESC');
        
        $roles = $qb->getQuery()->getResult();
        
        return $this->createCollection($roles);
    }
    
    /**
     * Najde role s vyšší nebo stejnou prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityHigherOrEqual(int $priority): Collection
    {
        return $this->findByPriority($priority, '>=');
    }
    
    /**
     * Najde role s nižší prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityLower(int $priority): Collection
    {
        return $this->findByPriority($priority, '<');
    }
    
    /**
     * Vrátí všechna oprávnění pro roli
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function getRolePermissions(int $roleId): Collection
    {
        $role = $this->find($roleId);
        if (!$role) {
            return $this->createCollection([]);
        }
        
        return $this->createCollection($role->getPermissions()->toArray());
    }
    
    /**
     * Zjistí, zda role má konkrétní oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        return $role->getPermissions()->contains($permission);
    }
    
    /**
     * Zjistí, zda role má všechna oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAllPermissions(int $roleId, array $permissionIds): bool
    {
        $role = $this->find($roleId);
        if (!$role) {
            return false;
        }
        
        $rolePermissions = $role->getPermissions();
        
        foreach ($permissionIds as $permissionId) {
            $permission = $this->entityManager->getReference(Permission::class, $permissionId);
            if (!$rolePermissions->contains($permission)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Zjistí, zda role má alespoň jedno oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAnyPermission(int $roleId, array $permissionIds): bool
    {
        $role = $this->find($roleId);
        if (!$role) {
            return false;
        }
        
        $rolePermissions = $role->getPermissions();
        
        foreach ($permissionIds as $permissionId) {
            $permission = $this->entityManager->getReference(Permission::class, $permissionId);
            if ($rolePermissions->contains($permission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Vrátí počet uživatelů s danou rolí
     * 
     * @param int $roleId
     * @return int
     */
    public function countUsers(int $roleId): int
    {
        $role = $this->find($roleId);
        if (!$role) {
            return 0;
        }
        
        return $role->getUsers()->count();
    }
    
    /**
     * Odstraní roli a všechny její oprávnění
     * 
     * @param int $roleId
     * @return bool
     */
    public function deleteWithPermissions(int $roleId): bool
    {
        $role = $this->find($roleId);
        if (!$role) {
            return false;
        }
        
        return $this->transaction(function() use ($role) {
            // Remove role from all users
            foreach ($role->getUsers() as $user) {
                $user->removeRole($role);
            }
            
            // Delete the role
            $this->entityManager->remove($role);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Vyhledá role podle komplexních kritérií
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
    ): PaginatedCollection
    {
        return $this->findWithFilters($criteria, $sortBy, $sortDir, $page, $itemsPerPage);
    }
}