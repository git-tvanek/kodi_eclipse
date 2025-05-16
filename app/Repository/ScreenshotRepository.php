<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Screenshot;
use App\Entity\Addon;
use App\Collection\Collection;
use App\Repository\Interface\IScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends BaseDoctrineRepository<Screenshot>
 */
class ScreenshotRepository extends BaseDoctrineRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Screenshot::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findByAddon(int $addonId): Collection
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $screenshots = $this->findBy(['addon' => $addon], ['sort_order' => 'ASC']);
        
        return new Collection($screenshots);
    }
    
    public function deleteByAddon(int $addonId): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb->delete(Screenshot::class, 's')
            ->where('s.addon = :addon')
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId))
            ->getQuery()
            ->execute();
            
        return $result;
    }
    
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $screenshot = $this->find($id);
        
        if (!$screenshot) {
            return false;
        }
        
        $screenshot->setSortOrder($sortOrder);
        $this->entityManager->flush();
        
        return true;
    }
    
    public function batchCreate(array $screenshots): int
    {
        $count = 0;
        
        foreach ($screenshots as $screenshot) {
            $this->entityManager->persist($screenshot);
            $count++;
        }
        
        $this->entityManager->flush();
        
        return $count;
    }
}