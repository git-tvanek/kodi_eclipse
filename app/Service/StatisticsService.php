<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AddonRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\AddonReviewRepository;
use App\Factory\Interface\IFactoryManager;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;

/**
 * Implementace služby pro statistiky
 * 
 * @implements IStatisticsService
 */
class StatisticsService implements IStatisticsService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /** @var CategoryRepository */
    private CategoryRepository $categoryRepository;
    
    /** @var AddonReviewRepository */
    private AddonReviewRepository $reviewRepository;
    
    /** @var Explorer */
    private Explorer $database;
    
    /** @var IFactoryManager */
    private IFactoryManager $factoryManager;
    
    /**
     * Konstruktor
     * 
     * @param AddonRepository $addonRepository
     * @param AuthorRepository $authorRepository
     * @param CategoryRepository $categoryRepository
     * @param AddonReviewRepository $reviewRepository
     * @param Explorer $database
     * @param IFactoryManager $factoryManager
     */
    public function __construct(
        AddonRepository $addonRepository,
        AuthorRepository $authorRepository,
        CategoryRepository $categoryRepository,
        AddonReviewRepository $reviewRepository,
        Explorer $database,
        IFactoryManager $factoryManager
    ) {
        $this->addonRepository = $addonRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
        $this->reviewRepository = $reviewRepository;
        $this->database = $database;
        $this->factoryManager = $factoryManager;
    }
    
    /**
     * Získá statistiky doplňků v průběhu času
     * 
     * @param string $interval 'day', 'week', 'month', nebo 'year'
     * @param int $limit Počet intervalů k vrácení
     * @param string $metric 'downloads', 'ratings', nebo 'addons'
     * @return array
     */
    public function getAddonStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array
    {
        return $this->addonRepository->getStatisticsOverTime($interval, $limit, $metric);
    }
    
    /**
     * Získá distribuci doplňků podle kategorie
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array
    {
        return $this->addonRepository->getAddonDistributionByCategory();
    }
    
    /**
     * Získá distribuci hodnocení
     * 
     * @return array
     */
    public function getRatingDistribution(): array
    {
        return $this->addonRepository->getRatingDistribution();
    }
    
    /**
     * Získá nejlepší autory podle počtu stažení
     * 
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array
    {
        return $this->addonRepository->getTopAuthorsByDownloads($limit);
    }
    
    /**
     * Získá statistiky pro dashboard
     * 
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        // Počet celkových doplňků
        $totalAddons = $this->database->table('addons')->count();
        
        // Počet celkových autorů
        $totalAuthors = $this->database->table('authors')->count();
        
        // Počet celkových kategorií
        $totalCategories = $this->database->table('categories')->count();
        
        // Počet celkových recenzí
        $totalReviews = $this->database->table('addon_reviews')->count();
        
        // Získat průměrné hodnocení
        $avgRating = $this->database->table('addon_reviews')
            ->select('AVG(rating) AS avg_rating')
            ->fetch();
        
        // Získat celkový počet stažení
        $totalDownloads = $this->database->table('addons')
            ->sum('downloads_count') ?? 0;
        
        // Získat nejnovější doplňky (posledních 30 dní)
        $thirtyDaysAgo = (new DateTime())->modify('-30 days');
        $newAddonsCount = $this->database->table('addons')
            ->where('created_at >= ?', $thirtyDaysAgo->format('Y-m-d H:i:s'))
            ->count();
        
        // Získat nejpopulárnější kategorie
        $popularCategories = $this->categoryRepository->getMostPopularCategories(5);
        
        // Získat nejnovější recenze
        $recentReviews = $this->reviewRepository->getMostRecentReviews(5);
        
        return [
            'totalAddons' => $totalAddons,
            'totalAuthors' => $totalAuthors,
            'totalCategories' => $totalCategories,
            'totalReviews' => $totalReviews,
            'totalDownloads' => (int)$totalDownloads,
            'averageRating' => $avgRating ? round((float)$avgRating->avg_rating, 2) : 0,
            'newAddonsCount' => $newAddonsCount,
            'popularCategories' => $popularCategories,
            'recentReviews' => $recentReviews
        ];
    }
}