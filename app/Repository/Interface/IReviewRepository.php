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
interface IReviewRepository extends IBaseRepository
{
    /**
     * Vytvoří novou recenzi doplňku
     * 
     * @param AddonReview $review Recenze k vytvoření
     * @return int ID vytvořené recenze
     */
    public function create(AddonReview $review): int;
    
    /**
     * Smaže recenzi doplňku
     * 
     * @param int $id ID recenze ke smazání
     * @return int Počet smazaných záznamů (0 nebo 1)
     */
    public function delete(int $id): int;
    
    /**
     * Najde recenze pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @return Collection<AddonReview> Kolekce recenzí
     */
    public function findByAddon(int $addonId): Collection;
    
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
     * Vrátí recenze s konkrétním hodnocením
     * 
     * @param int $rating Hodnocení (1-5)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Poskytuje analýzu sentimentu recenzí pro doplněk
     * 
     * @param int $addonId ID doplňku
     * @return array Výsledek analýzy obsahující počty pozitivních, neutrálních a negativních recenzí
     */
    public function getSentimentAnalysis(int $addonId): array;
    
    /**
     * Poskytuje časovou řadu aktivity recenzí v určitém intervalu
     * 
     * @param int $addonId ID doplňku
     * @param string $interval Časový interval ('day', 'week', 'month' nebo 'year')
     * @param int $limit Počet období k vrácení
     * @return array Pole s daty pro časovou řadu
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array;
    
    /**
     * Vrátí nejnovější recenze napříč všemi doplňky
     * 
     * @param int $limit Maximální počet recenzí
     * @return array Pole nejnovějších recenzí s informacemi o doplňcích
     */
    public function getMostRecentReviews(int $limit = 10): array;
    
    /**
     * Najde nejčastěji se opakující klíčová slova v komentářích
     * 
     * @param int $addonId ID doplňku
     * @param int $limit Maximální počet klíčových slov
     * @return array Pole klíčových slov s frekvencemi
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array;
}