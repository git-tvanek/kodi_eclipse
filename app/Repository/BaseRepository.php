<?php

declare(strict_types=1);

namespace App\Repository;

use App\Repository\Interface\IBaseRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * 100% kompatibilní modernizovaný BaseRepository
 * 
 * @template T of object
 * @extends EntityRepository<T>
 * @implements IBaseRepository<T>
 */
abstract class BaseRepository extends EntityRepository implements IBaseRepository
{
    protected ClassMetadata $metadata;
    protected string $defaultAlias = 'e';

    // ✅ Constructor Property Promotion (zachovává kompatibilitu)
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected string $entityClass
    ) {
        $this->metadata = $entityManager->getClassMetadata($entityClass);
        parent::__construct($entityManager, $this->metadata);
    }

    // =========================================================================
    // ZÁKLADNÍ CRUD OPERACE (nezměněno)
    // =========================================================================

    public function findAll(): iterable
    {
        return parent::findAll();
    }

    public function findById(int $id): ?object
    {
        return $this->find($id);
    }

    public function save(object $entity): int
    {
        $this->updateTimestamps($entity);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        return $entity->getId();
    }

    protected function updateEntity(object $entity): int
    {
        $this->updateTimestamps($entity, false);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        return $entity->getId();
    }

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

    // =========================================================================
    // VYHLEDÁVACÍ METODY (zachována kompatibilita)
    // =========================================================================

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    public function findBy(array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null): iterable
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        $qb = $this->applyArrayCriteria($qb, $criteria, $this->defaultAlias);
        
        // Apply ordering
        if ($this->hasProperty($orderColumn)) {
            $qb->orderBy("$this->defaultAlias.$orderColumn", $orderDir);
        } else {
            $qb->orderBy("$this->defaultAlias.id", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }

    public function findByUniqueAttribute(string $attribute, $value): ?object
    {
        return $this->findOneBy([$attribute => $value]);
    }

    public function findWithFilters(array $filters = [], string $sortBy = 'id', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        if ($this->hasProperty($sortBy)) {
            $qb->orderBy("$this->defaultAlias.$sortBy", $sortDir);
        } else {
            $qb->orderBy("$this->defaultAlias.id", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }

    public function findByRelation(string $relation, int $id, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->join("$this->defaultAlias.$relation", 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->orderBy("$this->defaultAlias.id", 'DESC');
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }

    public function getWithRelated(int $id, array $relations = []): ?array
    {
        $entity = $this->find($id);
        
        if (!$entity) {
            return null;
        }
        
        $result = ['entity' => $entity];
        
        foreach ($relations as $relation) {
            $getter = 'get' . ucfirst($relation);
            if (method_exists($entity, $getter)) {
                $relationData = $entity->$getter();
                
                if ($relationData instanceof \Doctrine\Common\Collections\Collection) {
                    $result[$relation] = $this->createCollection($relationData->toArray());
                } else {
                    $result[$relation] = $relationData;
                }
            }
        }
        
        return $result;
    }

    // =========================================================================
    // POČÍTÁNÍ A OVĚŘOVÁNÍ (nezměněno)
    // =========================================================================

    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->select("COUNT($this->defaultAlias.id)");
        
        $qb = $this->applyArrayCriteria($qb, $criteria, $this->defaultAlias);
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    public function existsByAttribute(string $attribute, $value): bool
    {
        return $this->findByUniqueAttribute($attribute, $value) !== null;
    }

    // =========================================================================
    // SOFT DELETE (nezměněno)
    // =========================================================================

    public function softDelete(int $id, ?string $reason = null): bool
    {
        $entity = $this->find($id);
        
        if (!$entity || !method_exists($entity, 'setIsDeleted')) {
            return false;
        }
        
        return $this->transaction(function() use ($entity, $reason) {
            $entity->setIsDeleted(true);
            
            if (method_exists($entity, 'setDeletedAt')) {
                $entity->setDeletedAt(new \DateTime());
            }
            
            if (method_exists($entity, 'setDeletionReason') && $reason !== null) {
                $entity->setDeletionReason($reason);
            }
            
            $this->updateTimestamps($entity, false);
            $this->entityManager->flush();
            return true;
        });
    }

    public function restore(int $id): bool
    {
        $entity = $this->find($id);
        
        if (!$entity || !method_exists($entity, 'setIsDeleted')) {
            return false;
        }
        
        return $this->transaction(function() use ($entity) {
            $entity->setIsDeleted(false);
            
            if (method_exists($entity, 'setDeletedAt')) {
                $entity->setDeletedAt(null);
            }
            
            if (method_exists($entity, 'setDeletionReason')) {
                $entity->setDeletionReason(null);
            }
            
            $this->updateTimestamps($entity, false);
            $this->entityManager->flush();
            return true;
        });
    }

    // =========================================================================
    // TRANSAKCE (s mixed return type - kompatibilní upgrade)
    // =========================================================================

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    // ✅ Jen přidaný return type - zůstává kompatibilní
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        
        try {
            $result = $callback();
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    // =========================================================================
    // FILTROVACÍ METODY (modernizováno ale kompatibilně)
    // =========================================================================

    /**
     * ✅ Match expression - ale zachovává stejnou logiku
     */
    protected function applyFilter(QueryBuilder $qb, string $field, $value, string $operator = 'eq', string $alias = 'e'): void
    {
        $paramName = str_replace('.', '_', $field) . '_' . md5(serialize($value));
        
        // ✅ Match místo switch - ale stejná funkcionalita
        match ($operator) {
            'eq' => $qb->andWhere("$alias.$field = :$paramName")
                       ->setParameter($paramName, $value),
                       
            'neq' => $qb->andWhere("$alias.$field != :$paramName")
                        ->setParameter($paramName, $value),
                        
            'lt' => $qb->andWhere("$alias.$field < :$paramName")
                       ->setParameter($paramName, $value),
                       
            'lte' => $qb->andWhere("$alias.$field <= :$paramName")
                        ->setParameter($paramName, $value),
                        
            'gt' => $qb->andWhere("$alias.$field > :$paramName")
                       ->setParameter($paramName, $value),
                       
            'gte' => $qb->andWhere("$alias.$field >= :$paramName")
                        ->setParameter($paramName, $value),
                        
            'in' => $qb->andWhere("$alias.$field IN (:$paramName)")
                       ->setParameter($paramName, (array)$value),
                       
            'nin' => $qb->andWhere("$alias.$field NOT IN (:$paramName)")
                        ->setParameter($paramName, (array)$value),
                        
            'like' => $qb->andWhere("$alias.$field LIKE :$paramName")
                         ->setParameter($paramName, '%' . $value . '%'),
                         
            'starts' => $qb->andWhere("$alias.$field LIKE :$paramName")
                           ->setParameter($paramName, $value . '%'),
                           
            'ends' => $qb->andWhere("$alias.$field LIKE :$paramName")
                         ->setParameter($paramName, '%' . $value),
                         
            'between' => is_array($value) && count($value) === 2
                ? $qb->andWhere("$alias.$field BETWEEN :min_$paramName AND :max_$paramName")
                     ->setParameter("min_$paramName", $value[0])
                     ->setParameter("max_$paramName", $value[1])
                : null, // ✅ Neházeme výjimku - zachováváme původní chování
                
            'isnull' => $value 
                ? $qb->andWhere("$alias.$field IS NULL")
                : $qb->andWhere("$alias.$field IS NOT NULL"),
                
            default => null // ✅ Neházeme výjimku - zachováváme původní chování
        };
    }

    /**
     * ✅ Zachovává původní logiku kontroly prázdných hodnot
     */
    protected function applyFilters(QueryBuilder $qb, array $filters, string $alias = 'e'): QueryBuilder
    {
        foreach ($filters as $key => $value) {
            // ✅ Stejná logika jako v původním kódu
            if ($value === null || $value === '' || $key === 'sort_by' || $key === 'sort_dir') {
                continue;
            }
            
            if (strpos($key, '_') === 0) {
                $this->applySpecialOperator($qb, $key, $value, $alias);
                continue;
            }
            
            // ✅ Zachovává původní parsing
            $parts = explode('__', $key);
            $field = $parts[0];
            $operator = $parts[1] ?? 'eq';
            
            if ($this->hasProperty($field)) {
                $this->applyFilter($qb, $field, $value, $operator, $alias);
            }
        }
        
        return $qb;
    }

    /**
     * ✅ Modernizované ale kompatibilní special operators
     */
    protected function applySpecialOperator(QueryBuilder $qb, string $operator, $value, string $alias = 'e'): void
    {
        // ✅ Match ale zachovává původní chování
        match ($operator) {
            '_join' => is_array($value) ? 
                array_walk($value, fn($relationAlias, $relation) => $qb->join("$alias.$relation", $relationAlias)) : null,
                
            '_leftJoin' => is_array($value) ?
                array_walk($value, fn($relationAlias, $relation) => $qb->leftJoin("$alias.$relation", $relationAlias)) : null,
                
            '_having' => is_array($value) ?
                array_walk($value, fn($condition) => $qb->having($condition)) : null,
                
            '_groupBy' => match (true) {
                is_array($value) => array_walk($value, fn($field) => $qb->addGroupBy("$alias.$field")),
                is_string($value) => $qb->addGroupBy("$alias.$value"),
                default => null
            },
            
            '_orderBy' => is_array($value) ?
                array_walk($value, fn($direction, $field) => $qb->addOrderBy("$alias.$field", $direction)) : null,
                
            '_search' => is_array($value) && isset($value['term']) && isset($value['fields']) ?
                $this->applySearchOperator($qb, $value['term'], $value['fields'], $alias) : null,
                
            default => null // ✅ Neházeme výjimku - zachováváme původní chování
        };
    }

    // =========================================================================
    // POMOCNÉ METODY (nezměněno)
    // =========================================================================

    protected function updateTimestamps(object $entity, bool $isNew = true): void
    {
        $now = new \DateTime();
        
        if ($isNew && method_exists($entity, 'setCreatedAt')) {
            $getCurrentCreatedAt = 'getCreatedAt';
            if (method_exists($entity, $getCurrentCreatedAt) && $entity->$getCurrentCreatedAt() === null) {
                $entity->setCreatedAt($now);
            }
        }
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
    }

    protected function paginate(QueryBuilder $qb, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $page = max(1, $page);
        
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

    protected function applyArrayCriteria(QueryBuilder $qb, array $criteria, string $alias = 'e'): QueryBuilder
    {
        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $qb->andWhere("$alias.$field IN (:$field)")
                   ->setParameter($field, $value);
            } else {
                $qb->andWhere("$alias.$field = :$field")
                   ->setParameter($field, $value);
            }
        }
        
        return $qb;
    }
    
    protected function applySearchOperator(QueryBuilder $qb, string $term, array $fields, string $alias = 'e'): QueryBuilder
    {
        if (empty($term) || empty($fields)) {
            return $qb;
        }
        
        $orX = $qb->expr()->orX();
        foreach ($fields as $field) {
            if ($this->hasProperty($field)) {
                $paramName = 'search_' . $field;
                $orX->add($qb->expr()->like("$alias.$field", ":$paramName"));
                $qb->setParameter($paramName, '%' . $term . '%');
            }
        }
        
        if ($orX->count() > 0) {
            $qb->andWhere($orX);
        }
        
        return $qb;
    }
    
    protected function hasProperty(string $property): bool
    {
        return $this->metadata->hasField($property) || $this->metadata->hasAssociation($property);
    }

    abstract protected function createCollection(array $entities): Collection;
}