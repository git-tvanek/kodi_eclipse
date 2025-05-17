<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Addon;
use App\Collection\Collection;
use App\Collection\AddonCollection;
use App\Collection\PaginatedCollection;
use Doctrine\ORM\QueryBuilder;

/**
 * Rozhraní pro repozitář doplňků
 * 
 * @extends IBaseRepository<Addon>
 */
interface IAddonRepository extends IBaseRepository
{
    /**
     * Najde doplněk podle slugu
     * 
     * @param string $slug Slug doplňku
     * @return Addon|null Doplněk nebo null, pokud nebyl nalezen
     */
    public function findBySlug(string $slug): ?Addon;
    
    /**
     * Najde doplňky podle autora
     * 
     * @param int $authorId ID autora
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon> Stránkovaná kolekce doplňků
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde doplňky v konkrétní kategorii
     * 
     * @param int $categoryId ID kategorie
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon> Stránkovaná kolekce doplňků
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde doplňky v kategorii a všech jejích podkategoriích
     * 
     * @param int $categoryId ID kategorie
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon> Stránkovaná kolekce doplňků
     */
    public function findByCategoryRecursive(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde nejstahovanější doplňky
     * 
     * @param int $limit Maximální počet vrácených doplňků
     * @return Collection<Addon> Kolekce doplňků
     */
    public function findPopular(int $limit = 10): Collection;
    
    /**
     * Najde nejlépe hodnocené doplňky
     * 
     * @param int $limit Maximální počet vrácených doplňků
     * @return Collection<Addon> Kolekce doplňků
     */
    public function findTopRated(int $limit = 10): Collection;
    
    /**
     * Najde nejnovější doplňky
     * 
     * @param int $limit Maximální počet vrácených doplňků
     * @return Collection<Addon> Kolekce doplňků
     */
    public function findNewest(int $limit = 10): Collection;
    
    /**
     * Najde podobné doplňky k zadanému doplňku
     * 
     * @param int $addonId ID doplňku, k němuž hledáme podobné
     * @param int $limit Maximální počet vrácených doplňků
     * @return Collection<Addon> Kolekce doplňků
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection;
    
    /**
     * Vyhledá doplňky podle klíčového slova
     * 
     * @param string $query Hledaný text
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon> Stránkovaná kolekce doplňků
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Pokročilé vyhledávání s možností filtrování
     * 
     * @param string $query Hledaný text
     * @param array $fields Pole názvů sloupců, ve kterých se má vyhledávat
     * @param array $filters Pole filtrů pro omezení výsledků
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon> Stránkovaná kolekce doplňků
     */
    public function advancedSearch(string $query, array $fields = ['name', 'description'], array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Zvýší počet stažení doplňku a zaloguje stažení
     * 
     * @param int $id ID doplňku
     * @param string|null $ipAddress IP adresa uživatele (volitelné)
     * @param string|null $userAgent User agent uživatele (volitelné)
     * @return int Počet aktualizovaných záznamů
     */
    public function incrementDownloadCount(int $id, ?string $ipAddress = null, ?string $userAgent = null): int;
    
    /**
     * Aktualizuje hodnocení doplňku
     * 
     * @param int $id ID doplňku
     */
    public function updateRating(int $id): void;
    
    /**
     * Vytvoří nový doplněk včetně souvisejících entit
     * 
     * @param Addon $addon Instance doplňku
     * @param array $screenshots Pole screenshotů
     * @param array $tagIds Pole ID tagů
     * @return int ID vytvořeného doplňku
     * @throws \Exception Při chybě v transakci
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int;
    
    /**
     * Aktualizuje doplněk včetně souvisejících entit
     * 
     * @param Addon $addon Instance doplňku
     * @param array $screenshots Pole screenshotů
     * @param array $tagIds Pole ID tagů
     * @return int ID aktualizovaného doplňku
     * @throws \Exception Při chybě v transakci
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int;
    
    /**
    * Načte doplněk včetně všech souvisejících entit
    * 
    * @param int $id ID doplňku
    * @param array $relations Seznam relací k načtení
    * @return array|null Pole s doplňkem a souvisejícími entitami, nebo null pokud doplněk neexistuje
    */
    public function getWithRelated(int $id, array $relations = []): ?array;
    
    /**
     * Získá statistiky stažení podle doplňků
     * 
     * @param int $limit Maximální počet doplňků k vrácení
     * @param \DateTime|null $startDate Počáteční datum pro filtrování
     * @return array Statistiky stažení
     */
    public function getDownloadsByAddon(int $limit = 10, ?\DateTime $startDate = null): array;
    
    /**
     * Získá statistiky stažení podle denní doby
     * 
     * @param \DateTime|null $startDate Počáteční datum pro filtrování
     * @return array Statistiky podle hodin
     */
    public function getDownloadsByHourOfDay(?\DateTime $startDate = null): array;
    
    /**
     * Získá statistiky doplňků v čase
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Počet intervalů k vrácení
     * @param string $metric 'downloads', 'ratings', or 'addons'
     * @return array Statistiky v čase
     */
    public function getStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array;
    
    /**
     * Získá distribuci doplňků podle kategorií
     *
     * @return array Distribuce doplňků
     */
    public function getAddonDistributionByCategory(): array;
    
    /**
     * Získá distribuci hodnocení
     *
     * @return array Distribuce hodnocení
     */
    public function getRatingDistribution(): array;
    
    /**
     * Získá nejlepší autory podle počtu stažení
     *
     * @param int $limit
     * @return array Nejlepší autoři
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array;
    
    /**
     * Vytvoří QueryBuilder pro filtrování doplňků
     * 
     * @param array $filters Pole filtrů
     * @return QueryBuilder Nakonfigurovaný query builder
     */
    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder;
}