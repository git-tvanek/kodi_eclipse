<?php

declare(strict_types=1);

namespace App\Facade;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Service\IAddonReviewService;

/**
 * Fasáda pro práci s recenzemi
 */
class ReviewFacade implements IFacade
{
    /** @var IReviewService */
    private IAddonReviewService $reviewService;
    
    /**
     * Konstruktor
     * 
     * @param IReviewService $reviewService
     */
    public function __construct(IAddonReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }
    
    /**
     * Vytvoří recenzi od přihlášeného uživatele
     * 
     * @param int $addonId ID doplňku
     * @param int $userId ID uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář
     * @return int ID vytvořené recenze
     */
    public function createUserReview(int $addonId, int $userId, int $rating, ?string $comment = null): int
    {
        return $this->reviewService->createFromUser($addonId, $userId, $rating, $comment);
    }
    
    /**
     * Vytvoří recenzi od anonymního uživatele
     * 
     * @param int $addonId ID doplňku
     * @param string $name Jméno uživatele
     * @param string|null $email E-mail uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář
     * @return int ID vytvořené recenze
     */
    public function createGuestReview(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): int
    {
        return $this->reviewService->createFromGuest($addonId, $name, $email, $rating, $comment);
    }
    
    /**
     * Získá recenze pro doplněk
     * 
     * @param int $addonId ID doplňku
     * @return Collection
     */
    public function getAddonReviews(int $addonId): Collection
    {
        return $this->reviewService->findByAddon($addonId);
    }
    
    /**
     * Najde recenze podle filtrů
     * 
     * @param array $filters Filtry
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection
     */
    public function findReviews(array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->reviewService->findWithFilters($filters, 'created_at', 'DESC', $page, $itemsPerPage);
    }
    
    /**
     * Získá analýzu sentimentu recenzí
     * 
     * @param int $addonId ID doplňku
     * @return array
     */
    public function getReviewSentiment(int $addonId): array
    {
        return $this->reviewService->getSentimentAnalysis($addonId);
    }
    
    /**
     * Získá aktivitu recenzí v průběhu času
     * 
     * @param int $addonId ID doplňku
     * @param string $interval Interval ('day', 'week', 'month', 'year')
     * @param int $limit Počet období
     * @return array
     */
    public function getReviewActivity(int $addonId, string $interval = 'month', int $limit = 12): array
    {
        return $this->reviewService->getReviewActivityOverTime($addonId, $interval, $limit);
    }
    
    /**
     * Získá nejnovější recenze
     * 
     * @param int $limit Počet recenzí
     * @return array
     */
    public function getRecentReviews(int $limit = 10): array
    {
        return $this->reviewService->getMostRecentReviews($limit);
    }
    
    /**
     * Získá recenze podle hodnocení
     * 
     * @param int $rating Hodnocení (1-5)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->reviewService->getReviewsByRating($rating, $page, $itemsPerPage);
    }
    
    /**
     * Najde běžná klíčová slova v recenzích
     * 
     * @param int $addonId ID doplňku
     * @param int $limit Počet klíčových slov
     * @return array
     */
    public function getReviewKeywords(int $addonId, int $limit = 10): array
    {
        return $this->reviewService->findCommonKeywords($addonId, $limit);
    }
    
    /**
     * Smaže recenzi
     * 
     * @param int $reviewId ID recenze
     * @return bool
     */
    public function deleteReview(int $reviewId): bool
    {
        return $this->reviewService->delete($reviewId);
    }
}