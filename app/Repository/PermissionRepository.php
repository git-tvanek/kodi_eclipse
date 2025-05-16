<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Permission;
use App\Entity\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IPermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends BaseDoctrineRepository<Permission>
 */
class PermissionRepository extends BaseDoctrineRepository implements IPermissionRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Permission::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        return $this->findOneBy([
            'resource' => $resource,
            'action' => $action
        ]);
    }
    
    public function findByRole(int $roleId): Collection
    {
        $role = $this->entityManager->getReference(Role::class, $roleId);
        $permissions = $role->getPermissions()->toArray();
        
        return new Collection($permissions);
    }
    
    public function findByUser(int $userId): Collection
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT p')
           ->from(Permission::class, 'p')
           ->join('p.roles', 'r')
           ->join('r.users', 'u')
           ->where('u.id = :userId')
           ->setParameter('userId', $userId);
        
        $permissions = $qb->getQuery()->getResult();
        
        return new Collection($permissions);
    }
    
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('p');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'resource':
                case 'action':
                    $qb->andWhere("p.$key LIKE :$key")
                       ->setParameter($key, '%' . $value . '%');
                    break;
                
                case 'role_id':
                    $qb->join('p.roles', 'r')
                       ->andWhere('r.id = :roleId')
                       ->setParameter('roleId', $value);
                    break;
                
                case 'user_id':
                    $qb->join('p.roles', 'r')
                       ->join('r.users', 'u')
                       ->andWhere('u.id = :userId')
                       ->setParameter('userId', $value);
                    break;
                
                default:
                    if (property_exists(Permission::class, $key)) {
                        $qb->andWhere("p.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Permission::class, $sortBy)) {
            $qb->orderBy("p.$sortBy", $sortDir);
        } else {
            $qb->orderBy('p.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function existsByResourceAndAction(string $resource, string $action): bool
    {
        return $this->findByResourceAndAction($resource, $action) !== null;
    }
    
    public function create(Permission $permission): int
    {
        // Check uniqueness of resource:action
        if ($this->existsByResourceAndAction($permission->getResource(), $permission->getAction())) {
            throw new \Exception("Permission for '{$permission->getResource()}:{$permission->getAction()}' already exists.");
        }
        
        $this->entityManager->persist($permission);
        $this->entityManager->flush();
        
        return $permission->getId();
    }
    
    public function update(Permission $permission): int
    {
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
    }
    
    public function findAllResources(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT p.resource')
           ->from(Permission::class, 'p')
           ->orderBy('p.resource', 'ASC');
        
        $result = $qb->getQuery()->getScalarResult();
        
        return array_column($result, 'resource');
    }
    
    public function findActionsByResource(string $resource): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.action')
           ->from(Permission::class, 'p')
           ->where('p.resource = :resource')
           ->setParameter('resource', $resource)
           ->orderBy('p.action', 'ASC');
        
        $result = $qb->getQuery()->getScalarResult();
        
        return array_column($result, 'action');
    }
    
    public function getRolesWithPermission(int $permissionId): Collection
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return new Collection([]);
        }
        
        $roles = $permission->getRoles()->toArray();
        
        return new Collection($roles);
    }
    
    public function countRolesWithPermission(int $permissionId): int
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return 0;
        }
        
        return $permission->getRoles()->count();
    }
    
    public function findByResource(string $resource): Collection
    {
        $permissions = $this->findBy(['resource' => $resource]);
        return new Collection($permissions);
    }
    
    public function deleteWithRoleBindings(int $permissionId): bool
    {
        $permission = $this->find($permissionId);
        if (!$permission) {
            return false;
        }
        
        $this->entityManager->beginTransaction();
        
        try {
            // Remove from all roles
            foreach ($permission->getRoles() as $role) {
                $role->removePermission($permission);
            }
            
            // Delete the permission
            $this->entityManager->remove($permission);
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
        $qb = $this->createQueryBuilder('p');
        
        // Apply criteria
        foreach ($criteria as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'resource':
                case 'action':
                case 'description':
                    $qb->andWhere("p.$key LIKE :$key")
                       ->setParameter($key, '%' . $value . '%');
                    break;
                
                case 'role_id':
                    $qb->join('p.roles', 'r')
                       ->andWhere('r.id = :roleId')
                       ->setParameter('roleId', $value);
                    break;
                
                default:
                    if (property_exists(Permission::class, $key)) {
                        $qb->andWhere("p.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Permission::class, $sortBy)) {
            $qb->orderBy("p.$sortBy", $sortDir);
        } else {
            $qb->orderBy('p.resource', 'ASC')
               ->addOrderBy('p.action', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
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