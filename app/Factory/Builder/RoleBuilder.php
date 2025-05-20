<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Role;
use App\Factory\RoleFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření rolí
 * 
 * @template-extends EntityBuilder<Role, RoleFactory>
 * @implements IEntityBuilder<Role>
 */
class RoleBuilder extends EntityBuilder
{
    /**
     * @param RoleFactory $factory
     */
    public function __construct(RoleFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví název role
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví kód role
     * 
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        return $this->setValue('code', $code);
    }
    
    /**
     * Nastaví popis role
     * 
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        return $this->setValue('description', $description);
    }
    
    /**
     * Nastaví prioritu role
     * 
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority): self
    {
        return $this->setValue('priority', $priority);
    }
    
    /**
     * Vytvoří administrátorskou roli
     * 
     * @return self
     */
    public function asAdmin(): self
    {
        $this->setValue('name', 'Administrátor');
        $this->setValue('code', 'admin');
        $this->setValue('description', 'Kompletní administrátor systému');
        return $this->setValue('priority', 100);
    }
    
    /**
     * Vytvoří moderátorskou roli
     * 
     * @return self
     */
    public function asModerator(): self
    {
        $this->setValue('name', 'Moderátor');
        $this->setValue('code', 'moderator');
        $this->setValue('description', 'Moderátor obsahu');
        return $this->setValue('priority', 50);
    }
    
    /**
     * Vytvoří běžnou uživatelskou roli
     * 
     * @return self
     */
    public function asUser(): self
    {
        $this->setValue('name', 'Uživatel');
        $this->setValue('code', 'user');
        $this->setValue('description', 'Běžný registrovaný uživatel');
        return $this->setValue('priority', 10);
    }
    
    /**
     * Vytvoří roli hosta
     * 
     * @return self
     */
    public function asGuest(): self
    {
        $this->setValue('name', 'Host');
        $this->setValue('code', 'guest');
        $this->setValue('description', 'Neregistrovaný uživatel');
        return $this->setValue('priority', 0);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Role
    {
        return $this->factory->createFromBuilder($this->data);
    }
}