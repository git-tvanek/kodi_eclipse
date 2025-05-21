<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Permission;
use App\Entity\Role;
use App\Factory\Interface\IPermissionFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Permission
 * 
 * @extends BaseFactory<Permission>
 * @implements IPermissionFactory<Permission>
 */
class PermissionFactory extends BaseFactory implements IPermissionFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Permission::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Permission
    {
        /** @var Permission $permission */
        $permission = $this->createNewInstance();
        return $this->createFromExisting($permission, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Permission
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['resource'])) {
            $entity->setResource($data['resource']);
        }
        
        if (isset($data['action'])) {
            $entity->setAction($data['action']);
        }
        
        if (isset($data['description'])) {
            $entity->setDescription($data['description']);
        }
        
        // Zpracování rolí
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $role) {
                if ($role instanceof Role) {
                    $entity->addRole($role);
                } elseif (is_array($role) && isset($role['id'])) {
                    /** @var Role $roleEntity */
                    $roleEntity = $this->getReference(Role::class, (int)$role['id']);
                    $entity->addRole($roleEntity);
                }
            }
        }
        
        return $entity;
    }
}