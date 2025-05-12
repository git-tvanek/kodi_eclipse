<?php

declare(strict_types=1);

namespace App\Facade;

use App\Service\IStatisticsService;

/**
 * Fasáda pro statistiky
 */
class StatisticsFacade implements IFacade
{
    /** @var IStatisticsService */
    private IStatisticsService $statisticsService;
    
    /**
     * Konstruktor
     * 
     * @param IStatisticsService $statisticsService
     */
    public function __construct(IStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }
    
    /**
     * Získá statistiky pro dashboard
     * 
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        return $this->statisticsService->getDashboardStatistics();
    }
    
    /**
     * Získá statistiky doplňků v průběhu času
     * 
     * @param string $interval Interval ('day', 'week', 'month', 'year')
     * @param int $limit Počet období
     * @param string $metric Metrika ('downloads', 'ratings', 'addons')
     * @return array
     */
    public function getAddonStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array
    {
        return $this->statisticsService->getAddonStatisticsOverTime($interval, $limit, $metric);
    }
    
    /**
     * Získá distribuci doplňků podle kategorie
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array
    {
        return $this->statisticsService->getAddonDistributionByCategory();
    }
    
    /**
     * Získá distribuci hodnocení
     * 
     * @return array
     */
    public function getRatingDistribution(): array
    {
        return $this->statisticsService->getRatingDistribution();
    }
    
    /**
     * Získá nejlepší autory podle počtu stažení
     * 
     * @param int $limit Počet autorů
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array
    {
        return $this->statisticsService->getTopAuthorsByDownloads($limit);
    }
}