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
 * @extends BaseDoctrineRepository<Role>
 */
class RoleRepository extends BaseRepository implements IRoleRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Role::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findByCode(string $code): ?Role
    {
        return $this->findOneBy(['code' => $code]);
    }
    
    public function getRoleWithPermissions(int $roleId): ?array
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return null;
        }
        
        return [
            'role' => $role,
            'permissions' => new Collection($role->getPermissions()->toArray())
        ];
    }
    
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('r');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'code':
                    $qb->andWhere("r.$key LIKE :$key")
                       ->setParameter($key, '%' . $value . '%');
                    break;
                
                case 'permission_id':
                    $qb->join('r.permissions', 'p')
                       ->andWhere('p.id = :permissionId')
                       ->setParameter('permissionId', $value);
                    break;
                
                case 'min_priority':
                    $qb->andWhere('r.priority >= :minPriority')
                       ->setParameter('minPriority', $value);
                    break;
                
                case 'max_priority':
                    $qb->andWhere('r.priority <= :maxPriority')
                       ->setParameter('maxPriority', $value);
                    break;
                
                default:
                    if (property_exists(Role::class, $key)) {
                        $qb->andWhere("r.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Role::class, $sortBy)) {
            $qb->orderBy("r.$sortBy", $sortDir);
        } else {
            $qb->orderBy('r.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function addRolePermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        // Check if already exists
        if ($role->getPermissions()->contains($permission)) {
            return true;
        }
        
        $role->addPermission($permission);
        $this->entityManager->flush();
        
        return true;
    }
    
    public function removeRolePermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        if (!$role->getPermissions()->contains($permission)) {
            return true;
        }
        
        $role->removePermission($permission);
        $this->entityManager->flush();
        
        return true;
    }
    
    public function findRolesByUser(int $userId): Collection
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('r')
           ->from(Role::class, 'r')
           ->join('r.users', 'u')
           ->where('u.id = :userId')
           ->setParameter('userId', $userId)
           ->orderBy('r.priority', 'DESC');
        
        $roles = $qb->getQuery()->getResult();
        
        return new Collection($roles);
    }
    
    public function existsByCode(string $code): bool
    {
        return $this->findByCode($code) !== null;
    }
    
    public function create(Role $role): int
    {
        // Check uniqueness of code
        if ($this->existsByCode($role->getCode())) {
            throw new \Exception("Role with code '{$role->getCode()}' already exists.");
        }
        
        $this->entityManager->persist($role);
        $this->entityManager->flush();
        
        return $role->getId();
    }
    
    public function update(Role $role): int
    {
        // Check uniqueness of code if changed
        $originalRole = $this->find($role->getId());
        
        if ($originalRole &&
            $originalRole->getCode() !== $role->getCode() &&
            $this->existsByCode($role->getCode())) {
            throw new \Exception("Role with code '{$role->getCode()}' already exists.");
        }
        
        $this->entityManager->persist($role);
        $this->entityManager->flush();
        
        return $role->getId();
    }
    
    public function findByPriority(int $priority, string $operator = '='): Collection
    {
        $qb = $this->createQueryBuilder('r');
        
        switch ($operator) {
            case '>':
                $qb->where('r.priority > :priority');
                break;
            case '>=':
                $qb->where('r.priority >= :priority');
                break;
            case '<':
                $qb->where('r.priority < :priority');
                break;
            case '<=':
                $qb->where('r.priority <= :priority');
                break;
            case '=':
            default:
                $qb->where('r.priority = :priority');
                break;
        }
        
        $qb->setParameter('priority', $priority)
           ->orderBy('r.priority', 'DESC');
        
        $roles = $qb->getQuery()->getResult();
        
        return new Collection($roles);
    }
    
    public function findByPriorityHigherOrEqual(int $priority): Collection
    {
        return $this->findByPriority($priority, '>=');
    }
    
    public function findByPriorityLower(int $priority): Collection
    {
        return $this->findByPriority($priority, '<');
    }
    
    public function getRolePermissions(int $roleId): Collection
    {
        $role = $this->find($roleId);
        if (!$role) {
            return new Collection([]);
        }
        
        return new Collection($role->getPermissions()->toArray());
    }
    
    public function hasPermission(int $roleId, int $permissionId): bool
    {
        $role = $this->find($roleId);
        $permission = $this->entityManager->getReference(Permission::class, $permissionId);
        
        if (!$role || !$permission) {
            return false;
        }
        
        return $role->getPermissions()->contains($permission);
    }
    
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
    
    public function countUsers(int $roleId): int
    {
        $role = $this->find($roleId);
        if (!$role) {
            return 0;
        }
        
        return $role->getUsers()->count();
    }
    
    public function deleteWithPermissions(int $roleId): bool
    {
        $role = $this->find($roleId);
        if (!$role) {
            return false;
        }
        
        $this->entityManager->beginTransaction();
        
        try {
            // Remove role from all users
            foreach ($role->getUsers() as $user) {
                $user->removeRole($role);
            }
            
            // Delete the role
            $this->entityManager->remove($role);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    public function search(array $criteria, string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('r');
        
        // Apply criteria
        foreach ($criteria as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'code':
                case 'description':
                    $qb->andWhere("r.$key LIKE :$key")
                       ->setParameter($key, '%' . $value . '%');
                    break;
                
                case 'permission_id':
                    $qb->join('r.permissions', 'p')
                       ->andWhere('p.id = :permissionId')
                       ->setParameter('permissionId', $value);
                    break;
                
                case 'min_priority':
                    $qb->andWhere('r.priority >= :minPriority')
                       ->setParameter('minPriority', $value);
                    break;
                
                case 'max_priority':
                    $qb->andWhere('r.priority <= :maxPriority')
                       ->setParameter('maxPriority', $value);
                    break;
                
                default:
                    if (property_exists(Role::class, $key)) {
                        $qb->andWhere("r.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Role::class, $sortBy)) {
            $qb->orderBy("r.$sortBy", $sortDir);
        } else {
            $qb->orderBy('r.priority', 'DESC')
               ->addOrderBy('r.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
}