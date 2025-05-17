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
        
        $metadata = $entityManager->getClassMetadata($entityClass);
        parent::__construct($entityManager, $metadata);
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
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)');
        
        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.$field = :$field")
               ->setParameter($field, $value);
        }
        
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

    // -------------------------------------------------------------------------
    // POMOCNÉ METODY
    // -------------------------------------------------------------------------

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
    * Vytvoří typovanou kolekci z pole entit
    * 
    * @param array<T> $entities Pole entit
    * @return Collection<T> Typovaná kolekce entit
    */
    abstract protected function createCollection(array $entities): Collection;
}