<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IBaseFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Základní implementace továrny
 * 
 * @template T
 * @implements IBaseFactory<T>
 */
abstract class BaseFactory implements IBaseFactory
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;
    
    /** @var string */
    protected string $entityClass;
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     * @param string $entityClass
     */
    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
    }
    
    /**
     * {@inheritDoc}
     */
    abstract public function create(array $data);
    
    /**
     * {@inheritDoc}
     */
    abstract public function createFromExisting($entity, array $data, bool $isNew = true);
    
    /**
     * {@inheritDoc}
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
    
    /**
     * Vytvoří novou instanci entity
     * 
     * @return T
     */
    protected function createNewInstance()
    {
        $className = $this->entityClass;
        return new $className();
    }
    
    /**
     * Získá referenci na entitu
     * 
     * @param string $entityClass Třída entity
     * @param int $id ID entity
     * @return object Reference na entitu
     */
    protected function getReference(string $entityClass, int $id): object
    {
        return $this->entityManager->getReference($entityClass, $id);
    }
}