<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Základní rozhraní pro továrny entit
 * 
 * @template T of object
 */
interface IBaseFactory
{
    /**
     * Vytvoří novou instanci entity
     * 
     * @param array $data
     * @return T
     * @throws \InvalidArgumentException Pokud data nejsou validní
     */
    public function create(array $data): object;
    
    /**
     * Vytvoří kopii existující entity s možností přepsání některých hodnot
     * 
     * @param T $entity
     * @param array $overrideData
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return T
     * @throws \InvalidArgumentException Pokud data nejsou validní
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): object;
    
    /**
     * Vrátí třídu entity, kterou továrna vytváří
     * 
     * @return string
     */
    public function getEntityClass(): string;
    
    /**
     * Aktualizuje časová razítka entity
     * 
     * @param T $entity
     * @param bool $isNew
     * @return T
     */
    public function updateTimestamps(object $entity, bool $isNew = true): object;
}