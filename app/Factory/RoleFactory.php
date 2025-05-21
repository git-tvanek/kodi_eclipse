<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Permission;
use App\Entity\Role;
use App\Factory\Interface\IRoleFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Role
 * 
 * @extends BaseFactory<Role>
 * @implements IRoleFactory<Role>
 */
class RoleFactory extends BaseFactory implements IRoleFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Role::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Role
    {
        /** @var Role $role */
        $role = $this->createNewInstance();
        return $this->createFromExisting($role, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Role
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['code'])) {
            $entity->setCode($data['code']);
        }
        
        if (isset($data['description'])) {
            $entity->setDescription($data['description']);
        }
        
        if (isset($data['priority'])) {
            $entity->setPriority((int)$data['priority']);
        }
        
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
            $entity->setCreatedAt($createdAt);
        } elseif ($isNew) {
            $entity->setCreatedAt(new DateTime());
        }
        
        if (isset($data['updated_at'])) {
            $updatedAt = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
            $entity->setUpdatedAt($updatedAt);
        } else {
            $entity->setUpdatedAt(new DateTime());
        }
        
        // Zpracování oprávnění
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $permission) {
                if ($permission instanceof Permission) {
                    $entity->addPermission($permission);
                } elseif (is_array($permission) && isset($permission['id'])) {
                    /** @var Permission $permissionEntity */
                    $permissionEntity = $this->getReference(Permission::class, (int)$permission['id']);
                    $entity->addPermission($permissionEntity);
                } elseif (is_string($permission)) {
                    // Pokud je oprávnění zadáno jako string (resource:action), vyhledáme ho v DB
                    $parts = explode(':', $permission);
                    if (count($parts) === 2) {
                        $permissionRepository = $this->entityManager->getRepository(Permission::class);
                        /** @var Permission|null $permissionEntity */
                        $permissionEntity = $permissionRepository->findOneBy([
                            'resource' => $parts[0],
                            'action' => $parts[1]
                        ]);
                        if ($permissionEntity) {
                            $entity->addPermission($permissionEntity);
                        }
                    }
                }
            }
        }
        
        return $entity;
    }
}