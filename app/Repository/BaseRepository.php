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
 * Základní repozitář pro Doctrine entity
 * 
 * @template T of object
 * @extends EntityRepository<T>
 * @implements IBaseRepository<T>
 */
abstract class BaseRepository extends EntityRepository implements IBaseRepository
{
    protected EntityManagerInterface $entityManager;
    protected string $entityClass;
    protected ClassMetadata $metadata;
    protected string $defaultAlias = 'e';

    // -------------------------------------------------------------------------
    // KONSTRUKTOR A INICIALIZACE
    // -------------------------------------------------------------------------
    
    /**
     * Konstruktor základního repozitáře
     * 
     * @param EntityManagerInterface $entityManager Entity Manager instance
     * @param string $entityClass Název třídy entity
     */
    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
        $this->metadata = $entityManager->getClassMetadata($entityClass);
        
        parent::__construct($entityManager, $this->metadata);
    }

    // -------------------------------------------------------------------------
    // ZÁKLADNÍ CRUD OPERACE
    // -------------------------------------------------------------------------

    /**
     * Vrátí všechny záznamy entity
     * 
     * @return iterable<T> Kolekce všech entit
     */
    public function findAll(): iterable
    {
        return parent::findAll();
    }

    /**
     * Najde entitu podle ID
     * 
     * @param int $id ID entity
     * @return T|null Entita nebo null, pokud nebyla nalezena
     */
    public function findById(int $id): ?object
    {
        return $this->find($id);
    }

    /**
     * Uloží novou nebo aktualizuje existující entitu
     * 
     * @param T $entity Entita k uložení
     * @return int ID uložené entity
     */
    public function save(object $entity): int
    {
        $this->updateTimestamps($entity);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        return $entity->getId();
    }

    /**
     * Aktualizuje existující entitu
     * 
     * @param T $entity Entita k aktualizaci
     * @return int ID aktualizované entity
     */
    protected function updateEntity(object $entity): int
    {
        $this->updateTimestamps($entity, false);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        return $entity->getId();
    }

    /**
     * Smaže entitu podle ID
     * 
     * @param int $id ID entity ke smazání
     * @return int Počet smazaných záznamů (0 nebo 1)
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

    // -------------------------------------------------------------------------
    // VYHLEDÁVACÍ METODY
    // -------------------------------------------------------------------------

    /**
     * Najde jeden záznam podle kritérií
     * 
     * @param array $criteria Kritéria vyhledávání
     * @param array|null $orderBy Kritéria řazení
     * @return T|null Entita nebo null, pokud nebyla nalezena
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * Najde záznamy podle kritérií
     * 
     * @param array $criteria Kritéria vyhledávání
     * @param array|null $orderBy Kritéria řazení
     * @param int|null $limit Maximální počet výsledků
     * @param int|null $offset Posun výsledků
     * @return iterable<T> Kolekce nalezených entit
     */
    public function findBy(array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null): iterable
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Najde záznamy se stránkováním
     * 
     * @param array $criteria Kritéria pro vyhledávání
     * @param int $page Číslo stránky (začíná od 1)
     * @param int $itemsPerPage Počet položek na stránku
     * @param string $orderColumn Sloupec pro řazení
     * @param string $orderDir Směr řazení (ASC nebo DESC)
     * @return PaginatedCollection<T> Stránkovaná kolekce entit
     */
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

    /**
     * Najde entity podle unikátního atributu
     * 
     * @param string $attribute Název atributu
     * @param mixed $value Hodnota atributu
     * @return T|null
     */
    public function findByUniqueAttribute(string $attribute, $value): ?object
    {
        return $this->findOneBy([$attribute => $value]);
    }

    /**
     * Vyhledá entity podle filtrů s podporou stránkování
     * 
     * @param array $filters Pole filtrů
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<T>
     */
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

    /**
     * Najde entity podle relace
     * 
     * @param string $relation Název relace
     * @param int $id ID related entity
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<T>
     */
    public function findByRelation(string $relation, int $id, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->join("$this->defaultAlias.$relation", 'r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->orderBy("$this->defaultAlias.id", 'DESC');
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }

    /**
     * Načte entitu včetně vztahů
     * 
     * @param int $id ID entity
     * @param array $relations Názvy relací k načtení
     * @return array|null
     */
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

    // -------------------------------------------------------------------------
    // METODY PRO POČÍTÁNÍ A OVĚŘOVÁNÍ
    // -------------------------------------------------------------------------

    /**
     * Spočítá záznamy podle kritérií
     * 
     * @param array $criteria Kritéria pro počítání záznamů
     * @return int Počet záznamů
     */
    public function count(array $criteria = []): int
    {
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->select("COUNT($this->defaultAlias.id)");
        
        $qb = $this->applyArrayCriteria($qb, $criteria, $this->defaultAlias);
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Ověří, zda existuje entita s daným ID
     * 
     * @param int $id ID entity k ověření
     * @return bool Výsledek ověření
     */
    public function exists(int $id): bool
    {
        return $this->find($id) !== null;
    }

    /**
     * Ověří, zda existuje entita s daným unikátním atributem
     * 
     * @param string $attribute Název atributu
     * @param mixed $value Hodnota atributu
     * @return bool
     */
    public function existsByAttribute(string $attribute, $value): bool
    {
        return $this->findByUniqueAttribute($attribute, $value) !== null;
    }

    // -------------------------------------------------------------------------
    // METODY PRO SOFT DELETE
    // -------------------------------------------------------------------------

    /**
     * Provede soft delete entity (pokud entita podporuje tuto funkci)
     * 
     * @param int $id ID entity
     * @param string|null $reason Důvod smazání
     * @return bool
     */
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

    /**
     * Obnoví soft-smazanou entitu
     * 
     * @param int $id ID entity
     * @return bool
     */
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

    // -------------------------------------------------------------------------
    // TRANSAKČNÍ METODY
    // -------------------------------------------------------------------------

    /**
     * Zahájí transakci
     */
    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    /**
     * Potvrdí transakci
     */
    public function commit(): void
    {
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    /**
     * Vrátí transakci
     */
    public function rollback(): void
    {
        $this->entityManager->rollback();
    }

    /**
     * Provede transakci s callbackem
     * 
     * @param callable $callback Callback, který se má provést v transakci
     * @return mixed Výsledek callbacku
     * @throws \Exception Při chybě v transakci
     */
    public function transaction(callable $callback)
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

    // -------------------------------------------------------------------------
    // POMOCNÉ METODY
    // -------------------------------------------------------------------------

    /**
     * Nastaví časová razítka entity (created_at, updated_at)
     * 
     * @param object $entity Entity k aktualizaci razítek
     * @param bool $isNew Příznak, zda jde o novou entitu (pro created_at)
     */
    protected function updateTimestamps(object $entity, bool $isNew = true): void
    {
        $now = new \DateTime();
        
        if ($isNew && method_exists($entity, 'setCreatedAt')) {
            // Nastavit created_at pouze pokud ještě nebylo nastaveno
            $getCurrentCreatedAt = 'getCreatedAt';
            if (method_exists($entity, $getCurrentCreatedAt) && $entity->$getCurrentCreatedAt() === null) {
                $entity->setCreatedAt($now);
            }
        }
        
        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }
    }

    /**
     * Pomocná metoda pro stránkování výsledků
     * 
     * @param QueryBuilder $qb Query builder instance
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<T> Stránkovaná kolekce entit
     */
    protected function paginate(QueryBuilder $qb, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Zajistit, že stránka je alespoň 1
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

    /**
     * Aplikuje filtry na QueryBuilder
     * 
     * @param QueryBuilder $qb
     * @param array $filters
     * @param string $alias
     * @return QueryBuilder
     */
    protected function applyFilters(QueryBuilder $qb, array $filters, string $alias = 'e'): QueryBuilder
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            if (strpos($key, '_') === 0) {
                // Speciální operátor začínající podtržítkem, např. _join, _having
                $this->applySpecialOperator($qb, $key, $value, $alias);
                continue;
            }
            
            // Rozdělit klíč na pole a operátor, např. "name__like" => ["name", "like"]
            $parts = explode('__', $key);
            $field = $parts[0];
            $operator = $parts[1] ?? 'eq';
            
            // Kontrola zda vlastnost existuje v entitě
            if ($this->hasProperty($field)) {
                $this->applyFilter($qb, $field, $value, $operator, $alias);
            }
        }
        
        return $qb;
    }

    /**
     * Aplikuje jednotlivý filtr
     * 
     * @param QueryBuilder $qb
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @param string $alias
     */
    protected function applyFilter(QueryBuilder $qb, string $field, $value, string $operator = 'eq', string $alias = 'e'): void
    {
        $paramName = str_replace('.', '_', $field) . '_' . md5(serialize($value));
        
        switch ($operator) {
            case 'eq':
                $qb->andWhere("$alias.$field = :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'neq':
                $qb->andWhere("$alias.$field != :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'lt':
                $qb->andWhere("$alias.$field < :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'lte':
                $qb->andWhere("$alias.$field <= :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'gt':
                $qb->andWhere("$alias.$field > :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'gte':
                $qb->andWhere("$alias.$field >= :$paramName")
                   ->setParameter($paramName, $value);
                break;
                
            case 'in':
                $qb->andWhere("$alias.$field IN (:$paramName)")
                   ->setParameter($paramName, (array)$value);
                break;
                
            case 'nin':
                $qb->andWhere("$alias.$field NOT IN (:$paramName)")
                   ->setParameter($paramName, (array)$value);
                break;
                
            case 'like':
                $qb->andWhere("$alias.$field LIKE :$paramName")
                   ->setParameter($paramName, '%' . $value . '%');
                break;
                
            case 'starts':
                $qb->andWhere("$alias.$field LIKE :$paramName")
                   ->setParameter($paramName, $value . '%');
                break;
                
            case 'ends':
                $qb->andWhere("$alias.$field LIKE :$paramName")
                   ->setParameter($paramName, '%' . $value);
                break;
                
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $qb->andWhere("$alias.$field BETWEEN :min_$paramName AND :max_$paramName")
                       ->setParameter("min_$paramName", $value[0])
                       ->setParameter("max_$paramName", $value[1]);
                }
                break;
                
            case 'isnull':
                if ($value) {
                    $qb->andWhere("$alias.$field IS NULL");
                } else {
                    $qb->andWhere("$alias.$field IS NOT NULL");
                }
                break;
                
            default:
                // Neznámý operátor - ignorujeme
                break;
        }
    }
    
    /**
     * Aplikuje speciální operátory pro QueryBuilder
     * 
     * @param QueryBuilder $qb
     * @param string $operator
     * @param mixed $value
     * @param string $alias
     */
    protected function applySpecialOperator(QueryBuilder $qb, string $operator, $value, string $alias = 'e'): void
    {
        switch ($operator) {
            case '_join':
                if (is_array($value)) {
                    foreach ($value as $relation => $relationAlias) {
                        $qb->join("$alias.$relation", $relationAlias);
                    }
                }
                break;
                
            case '_leftJoin':
                if (is_array($value)) {
                    foreach ($value as $relation => $relationAlias) {
                        $qb->leftJoin("$alias.$relation", $relationAlias);
                    }
                }
                break;
                
            case '_having':
                if (is_array($value)) {
                    foreach ($value as $havingCondition) {
                        $qb->having($havingCondition);
                    }
                }
                break;
                
            case '_groupBy':
                if (is_array($value)) {
                    foreach ($value as $groupByField) {
                        $qb->addGroupBy("$alias.$groupByField");
                    }
                } elseif (is_string($value)) {
                    $qb->addGroupBy("$alias.$value");
                }
                break;
                
            case '_orderBy':
                if (is_array($value)) {
                    foreach ($value as $field => $direction) {
                        $qb->addOrderBy("$alias.$field", $direction);
                    }
                }
                break;
                
            case '_search':
                if (is_array($value) && isset($value['term']) && isset($value['fields'])) {
                    $this->applySearchOperator($qb, $value['term'], $value['fields'], $alias);
                }
                break;
        }
    }
    
    /**
     * Aplikuje pole kritérií na QueryBuilder
     * 
     * @param QueryBuilder $qb
     * @param array $criteria
     * @param string $alias
     * @return QueryBuilder
     */
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
    
    /**
     * Aplikuje vyhledávání podle textu přes více polí
     * 
     * @param QueryBuilder $qb
     * @param string $term Hledaný výraz
     * @param array $fields Pole názvů sloupců pro hledání
     * @param string $alias
     * @return QueryBuilder
     */
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
    
    /**
     * Kontroluje, zda entita má danou vlastnost
     * 
     * @param string $property
     * @return bool
     */
    protected function hasProperty(string $property): bool
    {
        return $this->metadata->hasField($property) || $this->metadata->hasAssociation($property);
    }

    /**
     * Vytvoří typovanou kolekci z pole entit
     * 
     * @param array<T> $entities Pole entit
     * @return Collection<T> Typovaná kolekce entit
     */
    abstract protected function createCollection(array $entities): Collection;
}