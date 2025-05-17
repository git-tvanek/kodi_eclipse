<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\AddonReview;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář recenzí doplňků
 * 
 * @extends IBaseRepository<AddonReview>
 */
interface IAddonReviewRepository extends IBaseRepository
{
    /**
     * Vytvoří novou recenzi doplňku
     * 
     * @param AddonReview $review
     * @return int ID vytvořené recenze
     * @throws \Exception Při chybě při vytváření recenze
     */
    public function create(AddonReview $review): int;
    
    /**
     * Aktualizuje existující recenzi
     * 
     * @param AddonReview $review
     * @return int ID aktualizované recenze
     * @throws \Exception Při chybě při aktualizaci recenze
     */
    public function update(AddonReview $review): int;
    
    /**
     * Smaže recenzi doplňku
     * 
     * @param int $id
     * @return int Počet smazaných recenzí
     */
    public function delete(int $id): int;
    
    /**
     * Najde recenze pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @param bool $activeOnly Vrátit pouze aktivní recenze
     * @return Collection<AddonReview> Kolekce recenzí
     */
    public function findByAddon(int $addonId, bool $activeOnly = true): Collection;
    
    /**
     * Najde recenze vytvořené konkrétním uživatelem
     * 
     * @param int $userId ID uživatele
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function findByUser(int $userId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Vyhledá recenze podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'DESC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Poskytuje analýzu sentimentu recenzí pro doplněk
     * 
     * @param int $addonId ID doplňku
     * @param bool $activeOnly Počítat pouze aktivní recenze
     * @return array Výsledek analýzy obsahující počty pozitivních, neutrálních a negativních recenzí
     */
    public function getSentimentAnalysis(int $addonId, bool $activeOnly = true): array;
    
    /**
     * Poskytuje časovou řadu aktivity recenzí v určitém intervalu
     * 
     * @param int $addonId ID doplňku
     * @param string $interval Časový interval ('day', 'week', 'month' nebo 'year')
     * @param int $limit Počet období k vrácení
     * @param bool $activeOnly Počítat pouze aktivní recenze
     * @return array Pole s daty pro časovou řadu
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12, bool $activeOnly = true): array;
    
    /**
     * Najde nejčastěji se opakující klíčová slova v komentářích
     * 
     * @param int $addonId ID doplňku
     * @param int $limit Maximální počet klíčových slov
     * @param string $language Jazyk pro stop slova ('cs' nebo 'en')
     * @return array Pole klíčových slov s frekvencemi
     */
    public function findCommonKeywords(int $addonId, int $limit = 10, string $language = 'cs'): array;
    
    /**
     * Vrátí recenze s konkrétním hodnocením
     * 
     * @param int $rating Hodnocení (1-5)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Vrátí nejnovější recenze napříč všemi doplňky
     * 
     * @param int $limit Maximální počet recenzí
     * @return array Pole nejnovějších recenzí s informacemi o doplňcích
     */
    public function getMostRecentReviews(int $limit = 10): array;
    
    /**
     * Označí recenzi jako ověřenou/verifikovanou
     * 
     * @param int $reviewId ID recenze
     * @param bool $verified Stav ověření
     * @return bool Úspěch operace
     */
    public function setVerified(int $reviewId, bool $verified): bool;
    
    /**
     * Nastaví aktivitu recenze (zobrazování/skrytí)
     * 
     * @param int $reviewId ID recenze
     * @param bool $active Stav aktivity
     * @return bool Úspěch operace
     */
    public function setActive(int $reviewId, bool $active): bool;
    
    /**
     * Získá statistiky recenzí pro dashboard
     *
     * @return array Statistiky recenzí
     */
    public function getDashboardStatistics(): array;
}