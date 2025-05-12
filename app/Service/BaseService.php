<?php

declare(strict_types=1);

namespace App\Service;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\BaseRepository;

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
    
    /**
     * Konstruktor
     */
    public function __construct()
    {
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
        $results = $this->getRepository()->findAll();
        $entities = [];
        
        foreach ($results as $row) {
            $entities[] = call_user_func([$this->entityClass, 'fromArray'], $row->toArray());
        }
        
        return new Collection($entities);
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