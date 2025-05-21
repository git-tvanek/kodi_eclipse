<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Screenshot;

/**
 * Rozhraní pro továrnu ScreenshotFactory
 * 
 * @template-extends IBaseFactory<Screenshot>
 */
interface IScreenshotFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci screenshotu z pole dat
     * 
     * @param array $data Data pro vytvoření screenshotu
     * @return Screenshot Vytvořená instance
     */
    public function create(array $data): Screenshot;
    
    /**
     * Aktualizuje existující entitu screenshotu
     * 
     * @param Screenshot $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return Screenshot Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Screenshot;
}