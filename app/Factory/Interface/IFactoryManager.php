<?php

declare(strict_types=1);

namespace App\Factory\Interface;

/**
 * Rozhraní pro správce továren
 */
interface IFactoryManager
{
    /**
     * Registruje továrnu
     * 
     * @param IBaseFactory $factory
     * @return self
     */
    public function registerFactory(IBaseFactory $factory): self;
    
    /**
     * Vrátí továrnu pro daný typ entity
     * 
     * @param string $entityClass
     * @return IBaseFactory
     * @throws \InvalidArgumentException Pokud není továrna nalezena
     */
    public function getFactory(string $entityClass): IBaseFactory;
    
    /**
     * Vytvoří entitu daného typu
     * 
     * @param string $entityClass
     * @param array $data
     * @return object
     */
    public function create(string $entityClass, array $data): object;
    
    /**
     * Zkontroluje, zda existuje továrna pro daný typ entity
     * 
     * @param string $entityClass
     * @return bool
     */
    public function hasFactory(string $entityClass): bool;
}