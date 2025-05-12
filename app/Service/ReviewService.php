<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AddonReview;
use App\Repository\ReviewRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\ReviewFactory;

/**
 * Implementace služby pro recenze
 * 
 * @extends BaseService<AddonReview>
 * @implements IReviewService
 */
class ReviewService extends BaseService implements IReviewService
{
    /** @var ReviewRepository */
    private ReviewRepository $reviewRepository;
    
    /** @var ReviewFactory */
    private ReviewFactory $reviewFactory;
    
    /**
     * Konstruktor
     * 
     * @param ReviewRepository $reviewRepository
     * @param ReviewFactory $reviewFactory
     */
    public function __construct(
        ReviewRepository $reviewRepository,
        ReviewFactory $reviewFactory
    ) {
        parent::__construct();
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->entityClass = AddonReview::class;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return ReviewRepository
     */
    protected function getRepository(): ReviewRepository
    {
        return $this->reviewRepository;
    }
    
    /**
     * Vytvoří novou recenzi od přihlášeného uživatele
     * 
     * @param int $addonId ID doplňku
     * @param int $userId ID uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return int ID vytvořené recenze
     */
    public function createFromUser(int $addonId, int $userId, int $rating, ?string $comment = null): int
    {
        $review = $this->reviewFactory->createFromUser($addonId, $userId, $rating, $comment);
        return $this->reviewRepository->create($review);
    }
    
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
    public function createFromGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): int
    {
        $review = $this->reviewFactory->createFromGuest($addonId, $name, $email, $rating, $comment);
        return $this->reviewRepository->create($review);
    }
    
    /**
     * Najde recenze podle doplňku
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection
    {
        return $this->reviewRepository->findByAddon($addonId);
    }
    
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
    ): PaginatedCollection {
        return $this->reviewRepository->findWithFilters(
            $filters, 
            $sortBy, 
            $sortDir, 
            $page, 
            $itemsPerPage
        );
    }
    
    /**
     * Získá analýzu sentimentu
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array
    {
        return $this->reviewRepository->getSentimentAnalysis($addonId);
    }
    
    /**
     * Získá aktivitu recenzí v průběhu času
     * 
     * @param int $addonId
     * @param string $interval
     * @param int $limit
     * @return array
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array
    {
        return $this->reviewRepository->getReviewActivityOverTime($addonId, $interval, $limit);
    }
    
    /**
     * Získá nejnovější recenze
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array
    {
        return $this->reviewRepository->getMostRecentReviews($limit);
    }
    
    /**
     * Získá recenze podle hodnocení
     * 
     * @param int $rating
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->reviewRepository->getReviewsByRating($rating, $page, $itemsPerPage);
    }
    
    /**
     * Najde běžná klíčová slova v recenzích
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array
    {
        return $this->reviewRepository->findCommonKeywords($addonId, $limit);
    }
}