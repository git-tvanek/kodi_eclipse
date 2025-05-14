<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Repository\Interface\IBaseRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

/**
 * @template T of object
 * @extends EntityRepository<T>
 * @implements IBaseRepository<T>
 */
abstract class BaseDoctrineRepository extends EntityRepository implements IBaseRepository
{
    protected EntityManagerInterface $entityManager;
    protected string $entityClass;

    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
        
        $metadata = $entityManager->getClassMetadata($entityClass);
        parent::__construct($entityManager, $metadata);
    }

    /**
     * Get all records
     */
    public function findAll(): iterable
    {
        return parent::findAll();
    }

    /**
     * Get record by ID
     * 
     * @param int $id
     * @return T|null The entity or null if not found
     */
    public function findById(int $id): ?object
    {
        return $this->find($id);
    }

    /**
     * Find one record by given criteria
     * 
     * @param array $criteria
     * @return T|null The entity or null if not found
     */
    public function findOneBy(array $criteria): ?object
    {
        return parent::findOneBy($criteria);
    }

    /**
     * Find records by given criteria
     * 
     * @param array $criteria
     * @return iterable<T>
     */
    public function findBy(array $criteria = []): iterable
    {
        return parent::findBy($criteria);
    }

    /**
     * Create a new record or update existing
     * 
     * @param T $entity
     * @return int The ID of the record
     */
    public function save(object $entity): int
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        return $entity->getId();
    }

    /**
     * Delete a record
     * 
     * @param int $id
     * @return int Number of affected rows
     */
    public function delete(int $id): int
    {
        $entity = $this->find($id);
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
            return 1;
        }
        
        return 0;
    }

    /**
     * Count records based on criteria
     * 
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');
        
        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.$field = :$field")
               ->setParameter($field, $value);
        }
        
        return (int) $qb->getQuery()->getSingleScalarResult();
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
        $qb = $this->createQueryBuilder('e');
        
        // Apply criteria
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $qb->andWhere("e.$field IN (:$field)")
                   ->setParameter($field, $value);
            } else {
                $qb->andWhere("e.$field = :$field")
                   ->setParameter($field, $value);
            }
        }
        
        // Apply ordering
        $qb->orderBy("e.$orderColumn", $orderDir);
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }

    /**
     * Check if entity with given ID exists
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    /**
     * Execute a transaction with callback
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

    /**
     * Helper method to paginate results
     * 
     * @param QueryBuilder $qb
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<T>
     */
    protected function paginate(QueryBuilder $qb, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $paginator = new Paginator($qb);
        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $total = count($paginator);
        $pages = (int) ceil($total / $itemsPerPage);
        
        $entities = iterator_to_array($paginator->getIterator());
        $collection = $this->createCollection($entities);
        
        return new PaginatedCollection(
            $collection,
            $total,
            $page,
            $itemsPerPage,
            $pages
        );
    }

    /**
     * Create a collection from entities - to be overridden in child classes
     * 
     * @param array<T> $entities
     * @return Collection<T>
     */
    abstract protected function createCollection(array $entities): Collection;
}