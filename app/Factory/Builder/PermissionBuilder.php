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
     * @throws \InvalidArgumentException Pokud zdroj neodpovídá požadovanému formátu
     */
    public function setResource(string $resource): self
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $resource)) {
            throw new \InvalidArgumentException('Zdroj může obsahovat pouze písmena, čísla a podtržítka.');
        }
        return $this->setValue('resource', $resource);
    }
    
    /**
     * Nastaví akci oprávnění
     * 
     * @param string $action
     * @return self
     * @throws \InvalidArgumentException Pokud akce neodpovídá požadovanému formátu
     */
    public function setAction(string $action): self
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
            throw new \InvalidArgumentException('Akce může obsahovat pouze písmena, čísla a podtržítka.');
        }
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
     * {@inheritdoc}
     */
    public function build(): Permission
    {
        return $this->factory->createFromBuilder($this->data);
    }
}