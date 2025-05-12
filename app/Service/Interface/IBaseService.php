<?php

declare(strict_types=1);

namespace App\Service;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Základní rozhraní služby
 * 
 * @template T
 */
interface IBaseService
{
    /**
     * Najde entitu podle ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object;
    
    /**
     * Najde všechny entity
     * 
     * @return Collection<T>
     */
    public function findAll(): Collection;
    
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
    ): PaginatedCollection;
    
    /**
     * Uloží entitu (vytvoří nebo aktualizuje)
     * 
     * @param T $entity
     * @return int ID entity
     */
    public function save(object $entity): int;
    
    /**
     * Smaže entitu
     * 
     * @param int $id
     * @return bool Úspěch
     */
    public function delete(int $id): bool;
}