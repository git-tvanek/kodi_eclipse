<?php

declare(strict_types=1);

namespace App\Repository;

use App\Repository\Interface\IBaseRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Service\IBaseService;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

/**
 * @template T of object
 * @implements BaseRepositoryInterface<T>
 */
abstract class BaseRepository implements IBaseRepository
{
    use SmartObject;

    /** @var Explorer */
    protected Explorer $database;

    /** @var string */
    protected string $tableName;

    /** @var string The entity class name this repository manages */
    protected string $entityClass;

    public function __construct(Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Get all records
     * 
     * @return Selection
     */
    public function findAll(): Selection
    {
        return $this->getTable();
    }

    /**
     * Get record by ID
     * 
     * @param int $id
     * @return T|null The entity or null if not found
     */
    public function findById(int $id): ?object
    {
        $row = $this->getTable()->get($id);
        return $row ? $this->createEntity($row->toArray()) : null;
    }

    /**
     * Find one record by given criteria
     * 
     * @param array $criteria
     * @return T|null The entity or null if not found
     */
    public function findOneBy(array $criteria): ?object
    {
        $result = $this->findBy($criteria)->limit(1)->fetch();
        return $result ? $this->createEntity($result->toArray()) : null;
    }

    /**
     * Find records by given criteria
     * 
     * @param array $criteria
     * @return Selection
     */
    public function findBy(array $criteria = []): Selection
    {
        $selection = $this->getTable();
        foreach ($criteria as $key => $value) {
            $selection->where($key, $value);
        }
        return $selection;
    }

    /**
     * Create a new record or update existing
     * 
     * @param T $entity
     * @return int The ID of the record
     */
    public function save(object $entity): int
    {
        $data = $this->entityToArray($entity);
        
        // Remove ID for insertions
        if (!isset($entity->id)) {
            unset($data['id']);
        }
        
        if (isset($entity->id)) {
            // Update existing record
            $this->getTable()->wherePrimary($entity->id)->update($data);
            return $entity->id;
        } else {
            // Insert new record
            $row = $this->getTable()->insert($data);
            $id = $row->getPrimary();
            $entity->id = $id;
            return $id;
        }
    }

    /**
     * Delete a record
     * 
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete(int $id): int
    {
        return $this->getTable()->wherePrimary($id)->delete();
    }

    /**
     * Count records based on criteria
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int
    {
        return $this->findBy($criteria)->count();
    }

    /**
     * Find records with pagination
     * 
     * @param array $criteria
     * @param int $page
     * @param int $itemsPerPage
     * @param string $orderColumn
     * @param string $orderDir
     * @return PaginatedCollection<T>
     */
    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): PaginatedCollection
    {
        $selection = $this->findBy($criteria);
        $selection->order("$orderColumn $orderDir");
        
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert rows to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = $this->createEntity($row->toArray());
        }
        
        // Vytvoření typované kolekce
        $collection = $this->createCollection($items);
        
        // Zabalení do stránkované kolekce
        return new PaginatedCollection(
            $collection,
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }

    /**
     * Get the table
     * 
     * @return Selection
     */
    protected function getTable(): Selection
    {
        return $this->database->table($this->tableName);
    }
    
    /**
     * Create an entity instance from data array
     * 
     * @param array $data
     * @return T
     */
    protected function createEntity(array $data): object
    {
        return call_user_func([$this->entityClass, 'fromArray'], $data);
    }

    /**
     * Convert entity to array for database operations
     * 
     * @param T $entity
     * @return array
     */
    protected function entityToArray(object $entity): array
    {
        return $entity->toArray();
    }
    
    /**
     * Create a collection from entities
     * 
     * @param T[] $entities
     * @return Collection<T>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }

    /**
     * Kontroluje, zda entita s daným ID existuje
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->getTable()->wherePrimary($id)->count() > 0;
    }
    
    /**
     * Začíná transakci
     */
    public function beginTransaction(): void
    {
        $this->database->beginTransaction();
    }
    
    /**
     * Potvrzuje transakci
     */
    public function commit(): void
    {
        $this->database->commit();
    }
    
    /**
     * Vrací transakci
     */
    public function rollback(): void
    {
        $this->database->rollBack();
    }
    
    /**
     * Provede transakční operaci s callback funkcí
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}