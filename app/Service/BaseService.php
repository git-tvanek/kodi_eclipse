<?php

declare(strict_types=1);

namespace App\Service;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\BaseRepository;
use App\Factory\Interface\IFactoryManager;

/**
 * Základní implementace služby
 * 
 * @template T of object
 * @implements IBaseService<T>
 */
abstract class BaseService implements IBaseService
{
    /** @var string Název třídy entity */
    protected string $entityClass;
    
    /** @var IFactoryManager */
    protected IFactoryManager $factoryManager;
    
    /**
     * Konstruktor
     * 
     * @param IFactoryManager $factoryManager
     */
    public function __construct(IFactoryManager $factoryManager)
    {
        $this->factoryManager = $factoryManager;
    }
    
    /**
     * Najde entitu podle ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object
    {
        return $this->getRepository()->findById($id);
    }
    
    /**
     * Najde všechny entity
     * 
     * @return Collection<T>
     */
    public function findAll(): Collection
    {
        return $this->getRepository()->findAll();
    }
    
    /**
     * Najde entity s paginací
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return PaginatedCollection<T>
     */
    public function findWithPagination(
        array $criteria = [], 
        int $page = 1, 
        int $itemsPerPage = 10, 
        string $orderColumn = 'id', 
        string $orderDir = 'ASC'
    ): PaginatedCollection {
        return $this->getRepository()->findWithPagination(
            $criteria, 
            $page, 
            $itemsPerPage, 
            $orderColumn, 
            $orderDir
        );
    }
    
    /**
     * Vytvoří novou entitu
     * 
     * @param array $data
     * @return int ID vytvořené entity
     */
    public function create(array $data): int
    {
        // Použití obecné metody getFactoryForEntity pro získání správné továrny
        $factory = $this->factoryManager->getFactoryForEntity($this->entityClass);
        $entity = $factory->create($data);
        
        return $this->getRepository()->create($entity);
    }
    
    /**
     * Aktualizuje existující entitu
     * 
     * @param int $id
     * @param array $data
     * @return int ID aktualizované entity
     * @throws \Exception Pokud entita s daným ID neexistuje
     */
    public function update(int $id, array $data): int
    {
        $entity = $this->findById($id);
        
        if (!$entity) {
            throw new \Exception("Entita s ID {$id} nebyla nalezena.");
        }
        
        // Použití obecné metody getFactoryForEntity pro získání správné továrny
        $factory = $this->factoryManager->getFactoryForEntity($this->entityClass);
        $updatedEntity = $factory->createFromExisting($entity, $data, false);
        
        return $this->getRepository()->update($updatedEntity);
    }
    
    /**
     * Uloží entitu (vytvoří nebo aktualizuje)
     * 
     * @param T $entity
     * @return int ID entity
     */
    public function save(object $entity): int
    {
        return $this->getRepository()->save($entity);
    }
    
    /**
     * Smaže entitu
     * 
     * @param int $id
     * @return bool Úspěch
     */
    public function delete(int $id): bool
    {
        return $this->getRepository()->delete($id) > 0;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return BaseRepository<T>
     */
    abstract protected function getRepository();
}