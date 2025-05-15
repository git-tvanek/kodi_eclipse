<?php

declare(strict_types=1);

namespace App\Repository\Interface;

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
     * Vrátí všechny záznamy entity
     * 
     * @return iterable<T> Kolekce všech entit
     */
    public function findAll(): iterable;
    
    /**
     * Najde entitu podle ID
     * 
     * @param int $id ID entity
     * @return T|null Entita nebo null, pokud nebyla nalezena
     */
    public function findById(int $id): ?object;
    
/**
 * Najde jeden záznam podle kritérií
 * 
 * @param array $criteria Kritéria vyhledávání
 * @param array|null $orderBy Kritéria řazení
 * @return T|null Entita nebo null, pokud nebyla nalezena
 */
public function findOneBy(array $criteria, ?array $orderBy = null): ?object;

/**
 * Najde záznamy podle kritérií
 * 
 * @param array $criteria Kritéria vyhledávání
 * @param array|null $orderBy Kritéria řazení
 * @param int|null $limit Maximální počet výsledků
 * @param int|null $offset Posun výsledků
 * @return iterable<T> Kolekce nalezených entit
 */
public function findBy(array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null): iterable;
    
    /**
     * Uloží novou nebo aktualizuje existující entitu
     * 
     * @param T $entity Entita k uložení
     * @return int ID uložené entity
     */
    public function save(object $entity): int;
    
    /**
     * Smaže entitu podle ID
     * 
     * @param int $id ID entity ke smazání
     * @return int Počet smazaných záznamů (0 nebo 1)
     */
    public function delete(int $id): int;
    
    /**
     * Spočítá záznamy podle kritérií
     * 
     * @param array $criteria Kritéria pro počítání záznamů
     * @return int Počet záznamů
     */
    public function count(array $criteria = []): int;
    
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
    public function findWithPagination(array $criteria = [], int $page = 1, int $itemsPerPage = 10, string $orderColumn = 'id', string $orderDir = 'ASC'): PaginatedCollection;

    /**
     * Ověří, zda existuje entita s daným ID
     * 
     * @param int $id ID entity k ověření
     * @return bool Výsledek ověření
     */
    public function exists(int $id): bool;
    
    /**
     * Zahájí transakci
     */
    public function beginTransaction(): void;
    
    /**
     * Potvrdí transakci
     */
    public function commit(): void;
    
    /**
     * Vrátí transakci
     */
    public function rollback(): void;
    
    /**
     * Provede transakci s callbackem
     * 
     * @param callable $callback Callback, který se má provést v transakci
     * @return mixed Výsledek callbacku
     * @throws \Exception Při chybě v transakci
     */
    public function transaction(callable $callback);
}