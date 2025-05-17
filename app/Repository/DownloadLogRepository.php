<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DownloadLog;
use App\Entity\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IDownloadLogRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci se záznamy stažení
 * 
 * @extends BaseRepository<DownloadLog>
 */
class DownloadLogRepository extends BaseRepository implements IDownloadLogRepository
{
    protected string $defaultAlias = 'dl';
    
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
     * Vytvoří typovanou kolekci záznamů stažení
     * 
     * @param array<DownloadLog> $entities
     * @return Collection<DownloadLog>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
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
        return $this->transaction(function() use ($addon, $ipAddress, $userAgent) {
            $log = new DownloadLog();
            $log->setAddon($addon);
            $log->setIpAddress($ipAddress);
            $log->setUserAgent($userAgent);
            
            $this->updateTimestamps($log);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
            
            return $log->getId();
        });
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
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->select("COUNT($this->defaultAlias.id)")
            ->where("$this->defaultAlias.addon = :addon")
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId));
        
        if ($startDate !== null) {
            $qb->andWhere("$this->defaultAlias.created_at >= :startDate")
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate !== null) {
            $qb->andWhere("$this->defaultAlias.created_at <= :endDate")
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
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->where("$this->defaultAlias.addon = :addon")
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId))
            ->orderBy("$this->defaultAlias.created_at", 'DESC');
            
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
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->where("$this->defaultAlias.created_at >= :startDate")
            ->andWhere("$this->defaultAlias.created_at <= :endDate")
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy("$this->defaultAlias.created_at", 'DESC');
            
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
        $qb->select("HOUR($this->defaultAlias.created_at) as hour", "COUNT($this->defaultAlias.id) as download_count")
           ->from(DownloadLog::class, $this->defaultAlias);
        
        if ($startDate !== null) {
            $qb->where("$this->defaultAlias.created_at >= :startDate")
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
                $dbFormat = "CONCAT(YEAR($this->defaultAlias.created_at), '-', WEEK($this->defaultAlias.created_at))";
                $dateInterval = 'P1W';
                break;
            case 'month':
                $dateFormat = 'Y-m';
                $dbFormat = "DATE_FORMAT($this->defaultAlias.created_at, '%Y-%m')";
                $dateInterval = 'P1M';
                break;
            case 'year':
                $dateFormat = 'Y';
                $dbFormat = "YEAR($this->defaultAlias.created_at)";
                $dateInterval = 'P1Y';
                break;
            default:
                $dateFormat = 'Y-m';
                $dbFormat = "DATE_FORMAT($this->defaultAlias.created_at, '%Y-%m')";
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
        $qb->select("$dbFormat AS period", "COUNT($this->defaultAlias.id) AS download_count")
           ->from(DownloadLog::class, $this->defaultAlias)
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