<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Addon;

/**
 * Rozhraní pro továrnu AddonFactory
 * 
 * @template-extends IBaseFactory<Addon>
 */
interface IAddonFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci doplňku z pole dat
     * 
     * @param array $data Data pro vytvoření doplňku
     * @return Addon Vytvořená instance
     */
    public function create(array $data): Addon;
    
    /**
     * Aktualizuje existující entitu doplňku
     * 
     * @param Addon $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Addon Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Addon;
}