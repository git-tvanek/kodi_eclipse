<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AddonReview;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro recenze
 * 
 * @extends IBaseService<AddonReview>
 */
interface IReviewService extends IBaseService
{
    /**
     * Vytvoří novou recenzi od přihlášeného uživatele
     * 
     * @param int $addonId ID doplňku
     * @param int $userId ID uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return int ID vytvořené recenze
     */
    public function createFromUser(int $addonId, int $userId, int $rating, ?string $comment = null): int;
    
    /**
     * Vytvoří novou recenzi od anonymního uživatele
     * 
     * @param int $addonId ID doplňku
     * @param string $name Jméno uživatele
     * @param string|null $email E-mail uživatele (volitelný)
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return int ID vytvořené recenze
     */
    public function createFromGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): int;
    
    /**
     * Najde recenze podle doplňku
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection;
    
    /**
     * Najde recenze s filtry
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function findWithFilters(
        array $filters = [], 
        string $sortBy = 'created_at', 
        string $sortDir = 'DESC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
    /**
     * Získá analýzu sentimentu
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array;
    
    /**
     * Získá aktivitu recenzí v průběhu času
     * 
     * @param int $addonId
     * @param string $interval
     * @param int $limit
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
     * Najde běžná klíčová slova v recenzích
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array;
}