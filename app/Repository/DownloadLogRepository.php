<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DownloadLog;
use App\Entity\Addon;
use App\Repository\Interface\IDownloadLogRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci se záznamy stažení
 * 
 * @extends BaseDoctrineRepository<DownloadLog>
 */
class DownloadLogRepository extends BaseRepository implements IDownloadLogRepository
{
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, DownloadLog::class);
    }
    
    /**
     * Vytvoří nový záznam o stažení
     * 
     * @param Addon $addon Doplněk, který byl stažen
     * @param string|null $ipAddress IP adresa uživatele
     * @param string|null $userAgent User Agent uživatele
     * @return int ID vytvořeného záznamu
     */
    public function create(Addon $addon, ?string $ipAddress = null, ?string $userAgent = null): int
    {
        $log = new DownloadLog();
        $log->setAddon($addon);
        $log->setIpAddress($ipAddress);
        $log->setUserAgent($userAgent);
        
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        
        return $log->getId();
    }
    
    /**
     * Vrátí počet stažení doplňku za dané období
     * 
     * @param int $addonId ID doplňku
     * @param \DateTime|null $startDate Počáteční datum (null = bez omezení)
     * @param \DateTime|null $endDate Koncové datum (null = až do současnosti)
     * @return int Počet stažení
     */
    public function getDownloadCount(int $addonId, ?\DateTime $startDate = null, ?\DateTime $endDate = null): int
    {
        $qb = $this->createQueryBuilder('dl')
            ->select('COUNT(dl.id)')
            ->where('dl.addon = :addon')
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId));
        
        if ($startDate !== null) {
            $qb->andWhere('dl.created_at >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate !== null) {
            $qb->andWhere('dl.created_at <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Najde záznamy stažení pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<DownloadLog> Stránkovaná kolekce záznamů
     */
    public function findByAddon(int $addonId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('dl')
            ->where('dl.addon = :addon')
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId))
            ->orderBy('dl.created_at', 'DESC');
            
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Najde záznamy stažení za dané období
     * 
     * @param \DateTime $startDate Počáteční datum
     * @param \DateTime $endDate Koncové datum
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<DownloadLog> Stránkovaná kolekce záznamů
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('dl')
            ->where('dl.created_at >= :startDate')
            ->andWhere('dl.created_at <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('dl.created_at', 'DESC');
            
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Vrátí statistiku stažení podle denní doby
     * 
     * @param \DateTime|null $startDate Počáteční datum pro filtrování
     * @return array Statistiky podle hodin
     */
    public function getDownloadsByHourOfDay(?\DateTime $startDate = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('HOUR(dl.created_at) as hour', 'COUNT(dl.id) as download_count')
           ->from(DownloadLog::class, 'dl');
        
        if ($startDate !== null) {
            $qb->where('dl.created_at >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        $qb->groupBy('hour')
           ->orderBy('hour', 'ASC');
        
        $result = $qb->getQuery()->getResult();
        
        // Zajištění, že máme data pro všechny hodiny (0-23)
        $hourlyStats = array_fill(0, 24, ['hour' => 0, 'download_count' => 0]);
        
        foreach ($result as $row) {
            $hour = (int)$row['hour'];
            if ($hour >= 0 && $hour < 24) {
                $hourlyStats[$hour] = [
                    'hour' => $hour,
                    'download_count' => (int)$row['download_count']
                ];
            }
        }
        
        return array_values($hourlyStats);
    }
    
    /**
     * Vrátí statistiku stažení podle týdnů/měsíců
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Počet intervalů k vrácení
     * @return array Statistiky v čase
     */
    public function getDownloadStatisticsByInterval(string $interval = 'month', int $limit = 12): array
    {
        // Definice formátu data podle intervalu
        switch ($interval) {
            case 'day':
                $dateFormat = 'Y-m-d';
                $dbFormat = '%Y-%m-%d';
                $dateInterval = 'P1D';
                break;
            case 'week':
                $dateFormat = 'Y-W';
                $dbFormat = 'CONCAT(YEAR(dl.created_at), \'-\', WEEK(dl.created_at))';
                $dateInterval = 'P1W';
                break;
            case 'month':
                $dateFormat = 'Y-m';
                $dbFormat = 'DATE_FORMAT(dl.created_at, \'%Y-%m\')';
                $dateInterval = 'P1M';
                break;
            case 'year':
                $dateFormat = 'Y';
                $dbFormat = 'YEAR(dl.created_at)';
                $dateInterval = 'P1Y';
                break;
            default:
                $dateFormat = 'Y-m';
                $dbFormat = 'DATE_FORMAT(dl.created_at, \'%Y-%m\')';
                $dateInterval = 'P1M';
        }
        
        // Generování časových period
        $now = new \DateTime();
        $periods = [];
        $currentDate = clone $now;
        
        for ($i = 0; $i < $limit; $i++) {
            $periods[$currentDate->format($dateFormat)] = [
                'period' => $currentDate->format($dateFormat),
                'download_count' => 0
            ];
            
            $currentDate->sub(new \DateInterval($dateInterval));
        }
        
        // Seřadit periody chronologicky
        ksort($periods);
        
        // Získání dat z databáze
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$dbFormat AS period", 'COUNT(dl.id) AS download_count')
           ->from(DownloadLog::class, 'dl')
           ->groupBy('period')
           ->orderBy('period', 'ASC');
        
        $result = $qb->getQuery()->getResult();
        
        foreach ($result as $row) {
            if (isset($periods[$row['period']])) {
                $periods[$row['period']]['download_count'] = (int)$row['download_count'];
            }
        }
        
        return array_values($periods);
    }
}