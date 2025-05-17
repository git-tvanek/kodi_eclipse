<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\DownloadLog;
use App\Entity\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář záznamů stažení
 * 
 * @extends IBaseRepository<DownloadLog>
 */
interface IDownloadLogRepository extends IBaseRepository
{
    /**
     * Vytvoří nový záznam o stažení
     * 
     * @param Addon $addon Doplněk, který byl stažen
     * @param string|null $ipAddress IP adresa uživatele
     * @param string|null $userAgent User Agent uživatele
     * @return int ID vytvořeného záznamu
     */
    public function create(Addon $addon, ?string $ipAddress = null, ?string $userAgent = null): int;
    
    /**
     * Vrátí počet stažení doplňku za dané období
     * 
     * @param int $addonId ID doplňku
     * @param \DateTime|null $startDate Počáteční datum (null = bez omezení)
     * @param \DateTime|null $endDate Koncové datum (null = až do současnosti)
     * @return int Počet stažení
     */
    public function getDownloadCount(int $addonId, ?\DateTime $startDate = null, ?\DateTime $endDate = null): int;
    
    /**
     * Najde záznamy stažení pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<DownloadLog> Stránkovaná kolekce záznamů
     */
    public function findByAddon(int $addonId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde záznamy stažení za dané období
     * 
     * @param \DateTime $startDate Počáteční datum
     * @param \DateTime $endDate Koncové datum
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<DownloadLog> Stránkovaná kolekce záznamů
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Vrátí statistiku stažení podle denní doby
     * 
     * @param \DateTime|null $startDate Počáteční datum pro filtrování
     * @return array Statistiky podle hodin
     */
    public function getDownloadsByHourOfDay(?\DateTime $startDate = null): array;
    
    /**
     * Vrátí statistiku stažení podle týdnů/měsíců
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Počet intervalů k vrácení
     * @return array Statistiky v čase
     */
    public function getDownloadStatisticsByInterval(string $interval = 'month', int $limit = 12): array;
}

/**
 * Rozhraní pro repozitář screenshotů
 * 
 * @extends IBaseRepository<Screenshot>
 */
interface IScreenshotRepository extends IBaseRepository
{
    /**
     * Najde screenshoty pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @return Collection<Screenshot> Kolekce screenshotů
     */
    public function findByAddon(int $addonId): Collection;
    
    /**
     * Smaže všechny screenshoty doplňku
     * 
     * @param int $addonId ID doplňku
     * @return int Počet smazaných screenshotů
     */
    public function deleteByAddon(int $addonId): int;
    
    /**
     * Aktualizuje pořadí screenshotu
     * 
     * @param int $id ID screenshotu
     * @param int $sortOrder Nové pořadí
     * @return bool Úspěch operace
     */
    public function updateSortOrder(int $id, int $sortOrder): bool;
    
    /**
     * Vytvoří více screenshotů najednou
     * 
     * @param array $screenshots Pole objektů Screenshot
     * @return int Počet vytvořených screenshotů
     */
    public function batchCreate(array $screenshots): int;
    
    /**
     * Aktualizuje popis screenshotu
     * 
     * @param int $id ID screenshotu
     * @param string $description Nový popis
     * @return bool Úspěch operace
     */
    public function updateDescription(int $id, string $description): bool;
    
    /**
     * Aktualizuje URL obrázku screenshotu
     * 
     * @param int $id ID screenshotu
     * @param string $imageUrl Nová URL obrázku
     * @return bool Úspěch operace
     */
    public function updateImageUrl(int $id, string $imageUrl): bool;
    
    /**
     * Aktualizuje pořadí více screenshotů najednou
     * 
     * @param array $sortOrders Asociativní pole ID => pořadí
     * @return int Počet aktualizovaných screenshotů
     */
    public function batchUpdateSortOrder(array $sortOrders): int;
}