<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Rozhraní služby pro statistiky
 */
interface IStatisticsService
{
    /**
     * Získá statistiky doplňků v průběhu času
     * 
     * @param string $interval 'day', 'week', 'month', nebo 'year'
     * @param int $limit Počet intervalů k vrácení
     * @param string $metric 'downloads', 'ratings', nebo 'addons'
     * @return array
     */
    public function getAddonStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array;
    
    /**
     * Získá distribuci doplňků podle kategorie
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array;
    
    /**
     * Získá distribuci hodnocení
     * 
     * @return array
     */
    public function getRatingDistribution(): array;
    
    /**
     * Získá nejlepší autory podle počtu stažení
     * 
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array;
    
    /**
     * Získá statistiky pro dashboard
     * 
     * @return array
     */
    public function getDashboardStatistics(): array;
}