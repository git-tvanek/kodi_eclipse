<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Permission;
use App\Factory\PermissionFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření oprávnění
 * 
 * @template-extends EntityBuilder<Permission, PermissionFactory>
 * @implements IEntityBuilder<Permission>
 */
class PermissionBuilder extends EntityBuilder
{
    /**
     * @param PermissionFactory $factory
     */
    public function __construct(PermissionFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví název oprávnění
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví zdroj oprávnění
     * 
     * @param string $resource
     * @return self
     */
    public function setResource(string $resource): self
    {
        return $this->setValue('resource', $resource);
    }
    
    /**
     * Nastaví akci oprávnění
     * 
     * @param string $action
     * @return self
     */
    public function setAction(string $action): self
    {
        return $this->setValue('action', $action);
    }
    
    /**
     * Nastaví popis oprávnění
     * 
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        return $this->setValue('description', $description);
    }
    
    /**
     * Vytvoří CRUD oprávnění pro zdroj
     * 
     * @param string $resource Název zdroje
     * @return array<Permission> Pole vytvořených oprávnění
     */
    public function createCrudPermissions(string $resource): array
    {
        $permissions = [];
        
        // Create
        $createBuilder = clone $this;
        $createBuilder->setValue('name', "Vytvoření {$resource}");
        $createBuilder->setValue('resource', $resource);
        $createBuilder->setValue('action', 'create');
        $createBuilder->setValue('description', "Oprávnění pro vytvoření {$resource}");
        $permissions[] = $createBuilder->build();
        
        // Read
        $readBuilder = clone $this;
        $readBuilder->setValue('name', "Čtení {$resource}");
        $readBuilder->setValue('resource', $resource);
        $readBuilder->setValue('action', 'read');
        $readBuilder->setValue('description', "Oprávnění pro čtení {$resource}");
        $permissions[] = $readBuilder->build();
        
        // Update
        $updateBuilder = clone $this;
        $updateBuilder->setValue('name', "Úprava {$resource}");
        $updateBuilder->setValue('resource', $resource);
        $updateBuilder->setValue('action', 'update');
        $updateBuilder->setValue('description', "Oprávnění pro úpravu {$resource}");
        $permissions[] = $updateBuilder->build();
        
        // Delete
        $deleteBuilder = clone $this;
        $deleteBuilder->setValue('name', "Odstranění {$resource}");
        $deleteBuilder->setValue('resource', $resource);
        $deleteBuilder->setValue('action', 'delete');
        $deleteBuilder->setValue('description', "Oprávnění pro odstranění {$resource}");
        $permissions[] = $deleteBuilder->build();
        
        return $permissions;
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Permission
    {
        return $this->factory->createFromBuilder($this->data);
    }
}