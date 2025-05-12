<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\AddonReview;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář recenzí
 * 
 * @extends BaseRepositoryInterface<AddonReview>
 */
interface IReviewRepository extends IBaseRepository
{
    /**
     * Vytvoří novou recenzi
     * 
     * @param AddonReview $review
     * @return int
     */
    public function create(AddonReview $review): int;
    
    /**
     * Najde recenze podle doplňku
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection;
    
    /**
     * Najde recenze s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'DESC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Získá analýzu sentimentu recenzí
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array;
    
    /**
     * Získá aktivitu recenzí v průběhu času
     * 
     * @param int $addonId
     * @param string $interval 'day', 'week', 'month', nebo 'year'
     * @param int $limit Počet období k vrácení
     * @return array
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array;
    
    /**
     * Získá nejnovější recenze
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array;
    
    /**
     * Získá recenze podle hodnocení
     * 
     * @param int $rating
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde běžná klíčová slova v recenzích (základní textová analýza)
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array;
}