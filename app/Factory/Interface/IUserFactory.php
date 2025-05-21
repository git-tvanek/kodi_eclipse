<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\User;

/**
 * Rozhraní pro továrnu UserFactory
 * 
 * @template-extends IBaseFactory<User>
 */
interface IUserFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci uživatele z pole dat
     * 
     * @param array $data Data pro vytvoření uživatele
     * @return User Vytvořená instance
     */
    public function create(array $data): User;
    
    /**
     * Aktualizuje existující entitu uživatele
     * 
     * @param User $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return User Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): User;
}