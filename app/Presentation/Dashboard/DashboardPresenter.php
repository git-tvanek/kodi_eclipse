<?php

declare(strict_types=1);

namespace App\Presentation\Dashboard;

use App\Presentation\BasePresenter;
use App\Facade\StatisticsFacade;
use App\Facade\AddonFacade;
use App\Facade\CategoryFacade;
use App\Facade\AuthorFacade;
use App\Facade\ReviewFacade;
use App\Facade\TagFacade;

class DashboardPresenter extends BasePresenter
{
    /** @var StatisticsFacade */
    private StatisticsFacade $statisticsFacade;
    
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var CategoryFacade */
    private CategoryFacade $categoryFacade;
    
    /** @var AuthorFacade */
    private AuthorFacade $authorFacade;
    
    /** @var ReviewFacade */
    private ReviewFacade $reviewFacade;
    
    /** @var TagFacade */
    private TagFacade $tagFacade;
    
    /**
     * Constructor
     */
    public function __construct(
        StatisticsFacade $statisticsFacade,
        AddonFacade $addonFacade,
        CategoryFacade $categoryFacade,
        AuthorFacade $authorFacade,
        ReviewFacade $reviewFacade,
        TagFacade $tagFacade
    ) {
        $this->statisticsFacade = $statisticsFacade;
        $this->addonFacade = $addonFacade;
        $this->categoryFacade = $categoryFacade;
        $this->authorFacade = $authorFacade;
        $this->reviewFacade = $reviewFacade;
        $this->tagFacade = $tagFacade;
    }
    
    /**
     * Default action - show dashboard overview
     */
    public function renderDefault(): void
    {
        // Get dashboard statistics
        $dashboardStats = $this->statisticsFacade->getDashboardStatistics();
        
        // Get popular addons
        $popularAddons = $this->addonFacade->getPopularAddons(5);
        
        // Get top rated addons
        $topRatedAddons = $this->addonFacade->getTopRatedAddons(5);
        
        // Get newest addons
        $newestAddons = $this->addonFacade->getNewestAddons(5);
        
        // Get top authors
        $topAuthors = $this->authorFacade->getTopAuthors('downloads', 5);
        
        // Get trending tags
        $trendingTags = $this->tagFacade->getTrendingTags(30, 10);
        
        $this->template->stats = $dashboardStats;
        $this->template->popularAddons = $popularAddons;
        $this->template->topRatedAddons = $topRatedAddons;
        $this->template->newestAddons = $newestAddons;
        $this->template->topAuthors = $topAuthors;
        $this->template->trendingTags = $trendingTags;
    }
    
    /**
     * Addons statistics action
     */
    public function renderAddonStats(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): void
    {
        // Get addon statistics over time
        $addonStats = $this->statisticsFacade->getAddonStatisticsOverTime($interval, $limit, $metric);
        
        // Get addon distribution by category
        $addonDistribution = $this->statisticsFacade->getAddonDistributionByCategory();
        
        $this->template->addonStats = $addonStats;
        $this->template->addonDistribution = $addonDistribution;
        $this->template->interval = $interval;
        $this->template->metric = $metric;
    }
    
    /**
     * Category statistics action
     */
    public function renderCategoryStats(): void
    {
        // Get category hierarchy with statistics
        $categoryHierarchyStats = $this->categoryFacade->getCategoryHierarchyWithStats();
        
        // Get popular categories
        $popularCategories = $this->categoryFacade->getPopularCategories(10);
        
        $this->template->categoryStats = $categoryHierarchyStats;
        $this->template->popularCategories = $popularCategories;
    }
    
    /**
     * Author statistics action
     */
    public function renderAuthorStats(): void
    {
        // Get top authors by different metrics
        $topAuthorsByDownloads = $this->authorFacade->getTopAuthors('downloads', 10);
        $topAuthorsByAddons = $this->authorFacade->getTopAuthors('addons', 10);
        $topAuthorsByRating = $this->authorFacade->getTopAuthors('rating', 10);
        
        $this->template->topAuthorsByDownloads = $topAuthorsByDownloads;
        $this->template->topAuthorsByAddons = $topAuthorsByAddons;
        $this->template->topAuthorsByRating = $topAuthorsByRating;
    }
    
    /**
     * Review statistics action
     */
    public function renderReviewStats(): void
    {
        // Get rating distribution
        $ratingDistribution = $this->statisticsFacade->getRatingDistribution();
        
        // Get most recent reviews
        $recentReviews = $this->reviewFacade->getRecentReviews(10);
        
        $this->template->ratingDistribution = $ratingDistribution;
        $this->template->recentReviews = $recentReviews;
    }
    
    /**
     * Handle chart data AJAX request
     */
    public function handleGetChartData(string $chartType, string $interval = 'month', int $limit = 12, string $metric = 'downloads'): void
    {
        $result = [];
        
        if ($chartType === 'addonsOverTime') {
            // Get addon statistics over time
            $addonStats = $this->statisticsFacade->getAddonStatisticsOverTime($interval, $limit, $metric);
            $result = $addonStats;
        } elseif ($chartType === 'categoryDistribution') {
            // Get addon distribution by category
            $addonDistribution = $this->statisticsFacade->getAddonDistributionByCategory();
            $result = $addonDistribution;
        } elseif ($chartType === 'ratingDistribution') {
            // Get rating distribution
            $ratingDistribution = $this->statisticsFacade->getRatingDistribution();
            $result = $ratingDistribution;
        }
        
        // Send JSON response
        $this->sendJson($result);
    }
}