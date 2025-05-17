<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Screenshot;
use App\Entity\Addon;
use App\Collection\Collection;
use App\Repository\Interface\IScreenshotRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Repozitář pro práci se screenshoty doplňků
 * 
 * @extends BaseRepository<Screenshot>
 */
class ScreenshotRepository extends BaseRepository implements IScreenshotRepository
{
    protected string $defaultAlias = 's';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Screenshot::class);
    }
    
    /**
     * Vytvoří typovanou kolekci screenshotů
     * 
     * @param array<Screenshot> $entities
     * @return Collection<Screenshot>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde screenshoty pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @return Collection<Screenshot> Kolekce screenshotů
     */
    public function findByAddon(int $addonId): Collection
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $screenshots = $this->findBy(['addon' => $addon], ['sort_order' => 'ASC']);
        
        return $this->createCollection($screenshots);
    }
    
    /**
     * Smaže všechny screenshoty doplňku
     * 
     * @param int $addonId ID doplňku
     * @return int Počet smazaných screenshotů
     */
    public function deleteByAddon(int $addonId): int
    {
        return $this->transaction(function() use ($addonId) {
            $qb = $this->entityManager->createQueryBuilder();
            $result = $qb->delete(Screenshot::class, $this->defaultAlias)
                ->where("$this->defaultAlias.addon = :addon")
                ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId))
                ->getQuery()
                ->execute();
                
            return $result;
        });
    }
    
    /**
     * Aktualizuje pořadí screenshotu
     * 
     * @param int $id ID screenshotu
     * @param int $sortOrder Nové pořadí
     * @return bool Úspěch operace
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        $screenshot = $this->find($id);
        
        if (!$screenshot) {
            return false;
        }
        
        return $this->transaction(function() use ($screenshot, $sortOrder) {
            $screenshot->setSortOrder($sortOrder);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Vytvoří více screenshotů najednou
     * 
     * @param array $screenshots Pole objektů Screenshot
     * @return int Počet vytvořených screenshotů
     */
    public function batchCreate(array $screenshots): int
    {
        return $this->transaction(function() use ($screenshots) {
            $count = 0;
            
            foreach ($screenshots as $screenshot) {
                if ($screenshot instanceof Screenshot) {
                    $this->entityManager->persist($screenshot);
                    $count++;
                }
            }
            
            $this->entityManager->flush();
            
            return $count;
        });
    }
    
    /**
     * Aktualizuje popis screenshotu
     * 
     * @param int $id ID screenshotu
     * @param string $description Nový popis
     * @return bool Úspěch operace
     */
    public function updateDescription(int $id, string $description): bool
    {
        $screenshot = $this->find($id);
        
        if (!$screenshot) {
            return false;
        }
        
        return $this->transaction(function() use ($screenshot, $description) {
            $screenshot->setDescription($description);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Aktualizuje URL obrázku screenshotu
     * 
     * @param int $id ID screenshotu
     * @param string $imageUrl Nová URL obrázku
     * @return bool Úspěch operace
     */
    public function updateImageUrl(int $id, string $imageUrl): bool
    {
        $screenshot = $this->find($id);
        
        if (!$screenshot) {
            return false;
        }
        
        return $this->transaction(function() use ($screenshot, $imageUrl) {
            $screenshot->setUrl($imageUrl);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Aktualizuje pořadí více screenshotů najednou
     * 
     * @param array $sortOrders Asociativní pole ID => pořadí
     * @return int Počet aktualizovaných screenshotů
     */
    public function batchUpdateSortOrder(array $sortOrders): int
    {
        return $this->transaction(function() use ($sortOrders) {
            $count = 0;
            
            foreach ($sortOrders as $id => $sortOrder) {
                $screenshot = $this->find($id);
                if ($screenshot) {
                    $screenshot->setSortOrder($sortOrder);
                    $count++;
                }
            }
            
            $this->entityManager->flush();
            
            return $count;
        });
    }
}