<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IBaseFactory;
use App\Factory\Interface\IFactoryManager;

/**
 * Správce továren
 */
class FactoryManager implements IFactoryManager
{
    /** @var array<string, IBaseFactory> */
    private array $factories = [];
    
    /**
     * {@inheritdoc}
     */
    public function registerFactory(IBaseFactory $factory): self
    {
        $entityClass = $factory->getEntityClass();
        $this->factories[$entityClass] = $factory;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFactory(string $entityClass): IBaseFactory
    {
        if (!$this->hasFactory($entityClass)) {
            throw new \InvalidArgumentException("No factory registered for entity class $entityClass");
        }
        
        return $this->factories[$entityClass];
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(string $entityClass, array $data): object
    {
        return $this->getFactory($entityClass)->create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasFactory(string $entityClass): bool
    {
        return isset($this->factories[$entityClass]);
    }
    
    /**
     * Vrátí všechny registrované továrny
     * 
     * @return array<string, IBaseFactory>
     */
    public function getFactories(): array
    {
        return $this->factories;
    }
    
    /**
     * Vytvoří entitu pomocí builderu
     * 
     * @param string $entityClass
     * @param callable $builderSetup
     * @return object
     * @throws \InvalidArgumentException Pokud továrna nepodporuje buildery
     */
    public function createWithBuilder(string $entityClass, callable $builderSetup): object
    {
        $factory = $this->getFactory($entityClass);
        
        if (!($factory instanceof \App\Factory\Interface\IBuilderFactory)) {
            throw new \InvalidArgumentException(
                "Factory for entity class $entityClass does not support builders"
            );
        }
        
        $builder = $factory->createBuilder();
        $builderSetup($builder);
        
        return $builder->build();
    }
}