<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use Nette\Database\Table\Selection;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Základní rozhraní pro všechny repozitáře
 * 
 * @template T
 */
interface IBaseRepository
{
    /**
     * Získá všechny záznamy
     * 
     * @return Selection
     */
    public function findAll(): Selection;
    
    /**
     * Najde záznam podle ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object;
    
    /**
     * Najde jeden záznam podle daných kritérií
     * 
     * @param array $criteria
     * @return T|null
     */
    public function findOneBy(array $criteria): ?object;
    
    /**
     * Najde záznamy podle daných kritérií
     * 
     * @param array $criteria
     * @return Selection
     */
    public function findBy(array $criteria = []): Selection;
    
    /**
     * Uloží entitu (vytvoří nebo aktualizuje)
     * 
     * @param T $entity
     * @return int ID záznamu
     */
    public function save(object $entity): int;
    
    /**
     * Odstraní záznam
     * 
     * @param int $id
     * @return int Počet ovlivněných řádků
     */
    public function delete(int $id): int;
    
    /**
     * Spočítá záznamy podle kritérií
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int;
    
    /**
     * Najde záznamy s paginací
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return PaginatedCollection<T>
     */
    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): PaginatedCollection;
}