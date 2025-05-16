<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\DownloadLog;
use App\Entity\Addon;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci se záznamy stažení
 * 
 * @extends BaseDoctrineRepository<DownloadLog>
 */
class DownloadLogRepository extends BaseRepository
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
}