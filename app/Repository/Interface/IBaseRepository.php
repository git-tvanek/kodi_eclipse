<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Modernizovaný interface pro base repository s PHP 8+ features
 * 
 * @template T of object
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
     * @param array|null $orderBy ✅ Union type místo ?array
     * @return T|null
     */
    public function findOneBy(array $criteria, array|null $orderBy = null): ?object;
    
    /**
     * Finds entities by criteria
     * 
     * @param array $criteria
     * @param array|null $orderBy ✅ Union type
     * @param int|null $limit ✅ Union type místo mixed
     * @param int|null $offset ✅ Union type místo mixed
     * @return iterable<T>
     */
    public function findBy(
        array $criteria = [], 
        array|null $orderBy = null, 
        int|null $limit = null, 
        int|null $offset = null
    ): iterable;
    
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
    public function findWithPagination(
        array $criteria = [], 
        int $page = 1, 
        int $itemsPerPage = 10, 
        string $orderColumn = 'id', 
        string $orderDir = 'ASC'
    ): PaginatedCollection;
    
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
     * @param string|int|float|bool $value ✅ Union types pro flexible hodnoty
     * @return T|null
     */
    public function findByUniqueAttribute(string $attribute, string|int|float|bool $value): ?object;
    
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
    public function findWithFilters(
        array $filters = [], 
        string $sortBy = 'id', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
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
     * @return array|null ✅ Explicitní nullable array
     */
    public function getWithRelated(int $id, array $relations = []): array|null;
    
    /**
     * Checks if entity exists by attribute
     * 
     * @param string $attribute
     * @param string|int|float|bool $value ✅ Union types
     * @return bool
     */
    public function existsByAttribute(string $attribute, string|int|float|bool $value): bool;
    
    /**
     * Soft deletes entity
     * 
     * @param int $id
     * @param string|null $reason ✅ Union type místo ?string
     * @return bool
     */
    public function softDelete(int $id, string|null $reason = null): bool;
    
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
     * @return mixed ✅ KLÍČOVÁ ZMĚNA - přidaný mixed return type
     */
    public function transaction(callable $callback): mixed;
}