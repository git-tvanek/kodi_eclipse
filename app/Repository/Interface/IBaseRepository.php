<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use Doctrine\ORM\QueryBuilder;

/**
 * Comprehensive interface for base repository functionality
 * 
 * @template T
 */
interface IBaseRepository
{
    /**
     * Finds all entities
     * 
     * @return iterable<T>
     */
    public function findAll(): iterable;
    
    /**
     * Finds entity by ID
     * 
     * @param int $id
     * @return T|null
     */
    public function findById(int $id): ?object;
    
    /**
     * Finds one entity by criteria
     * 
     * @param array $criteria
     * @param array|null $orderBy
     * @return T|null
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object;
    
    /**
     * Finds entities by criteria
     * 
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return iterable<T>
     */
    public function findBy(array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null): iterable;
    
    /**
     * Saves entity
     * 
     * @param T $entity
     * @return int Entity ID
     */
    public function save(object $entity): int;
    
    /**
     * Deletes entity by ID
     * 
     * @param int $id
     * @return int Number of deleted entities
     */
    public function delete(int $id): int;
    
    /**
     * Counts entities by criteria
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int;
    
    /**
     * Finds with pagination
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return PaginatedCollection<T>
     */
    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): PaginatedCollection;
    
    /**
     * Checks if entity exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;
    
    /**
     * Finds entity by unique attribute
     * 
     * @param string $attribute
     * @param mixed $value
     * @return T|null
     */
    public function findByUniqueAttribute(string $attribute, $value): ?object;
    
    /**
     * Finds with advanced filters
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<T>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'id', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Finds by relation
     * 
     * @param string $relation
     * @param int $id
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<T>
     */
    public function findByRelation(string $relation, int $id, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Gets entity with related data
     * 
     * @param int $id
     * @param array $relations
     * @return array|null
     */
    public function getWithRelated(int $id, array $relations = []): ?array;
    
    /**
     * Checks if entity exists by attribute
     * 
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function existsByAttribute(string $attribute, $value): bool;
    
    /**
     * Soft deletes entity
     * 
     * @param int $id
     * @param string|null $reason
     * @return bool
     */
    public function softDelete(int $id, ?string $reason = null): bool;
    
    /**
     * Restores soft-deleted entity
     * 
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool;
    
    /**
     * Begins transaction
     */
    public function beginTransaction(): void;
    
    /**
     * Commits transaction
     */
    public function commit(): void;
    
    /**
     * Rolls back transaction
     */
    public function rollback(): void;
    
    /**
     * Executes transaction with callback
     * 
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback);
}