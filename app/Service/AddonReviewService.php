<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AddonReview;
use App\Repository\AddonReviewRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\Interface\IFactoryManager;

/**
 * Implementace služby pro recenze
 * 
 * @extends BaseService<AddonReview>
 * @implements IAddonReviewService
 */
class AddonReviewService extends BaseService implements IAddonReviewService
{
    /** @var AddonReviewRepository */
    private AddonReviewRepository $addonReviewRepository;
    
    /**
     * Konstruktor
     */
    public function __construct(
        AddonReviewRepository $addonReviewRepository,
        IFactoryManager $factoryManager
    ) {
        parent::__construct($factoryManager);
        $this->addonReviewRepository = $addonReviewRepository;
        $this->entityClass = AddonReview::class;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return AddonReviewRepository
     */
    protected function getRepository(): AddonReviewRepository
    {
        return $this->addonReviewRepository;
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
        $data = [
            'addon_id' => $addonId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified' => true // Přihlášený uživatel je automaticky verifikovaný
        ];
        
        $review = $this->factoryManager->createAddonReview($data);
        return $this->addonReviewRepository->create($review);
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
        $data = [
            'addon_id' => $addonId,
            'name' => $name,
            'email' => $email,
            'rating' => $rating,
            'comment' => $comment,
            'is_verified' => false // Anonymní uživatel není automaticky verifikovaný
        ];
        
        $review = $this->factoryManager->createAddonReview($data);
        return $this->addonReviewRepository->create($review);
    }
    
    /**
     * Najde recenze podle doplňku
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection
    {
        return $this->addonReviewRepository->findByAddon($addonId);
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
        return $this->addonReviewRepository->findWithFilters(
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
        return $this->addonReviewRepository->getSentimentAnalysis($addonId);
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
        return $this->addonReviewRepository->getReviewActivityOverTime($addonId, $interval, $limit);
    }
    
    /**
     * Získá nejnovější recenze
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array
    {
        return $this->addonReviewRepository->getMostRecentReviews($limit);
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
        return $this->addonReviewRepository->getReviewsByRating($rating, $page, $itemsPerPage);
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
        return $this->addonReviewRepository->findCommonKeywords($addonId, $limit);
    }
}