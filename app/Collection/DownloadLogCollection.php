<?php

namespace App\Collection;

use App\Entity\DownloadLog;

/**
 * Typovaná kolekce pro záznamy stažení
 * 
 * @extends Collection<DownloadLog>
 */
class DownloadLogCollection extends Collection
{
    /**
     * Seřadí podle data stažení
     */
    public function sortByCreatedAt(string $direction = 'DESC'): self
    {
        return $this->sort(function(DownloadLog $a, DownloadLog $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getCreatedAt() <=> $a->getCreatedAt()
                : $a->getCreatedAt() <=> $b->getCreatedAt();
        });
    }
    
    /**
     * Filtruje podle konkrétního doplňku
     */
    public function filterByAddon(int $addonId): self
    {
        return $this->filter(function(DownloadLog $log) use ($addonId): bool {
            return $log->getAddon()->getId() === $addonId;
        });
    }
    
    /**
     * Filtruje podle IP adresy
     */
    public function filterByIpAddress(string $ipAddress): self
    {
        return $this->filter(function(DownloadLog $log) use ($ipAddress): bool {
            return $log->getIpAddress() === $ipAddress;
        });
    }
    
    /**
     * Filtruje podle časového rozmezí
     */
    public function filterByDateRange(\DateTime $from, \DateTime $to): self
    {
        return $this->filter(function(DownloadLog $log) use ($from, $to): bool {
            $created = $log->getCreatedAt();
            return $created >= $from && $created <= $to;
        });
    }
    
    /**
     * Seskupí podle doplňků s počty stažení
     */
    public function groupByAddon(): array
    {
        $groups = [];
        
        foreach ($this as $log) {
            $addonId = $log->getAddon()->getId();
            if (!isset($groups[$addonId])) {
                $groups[$addonId] = [
                    'addon' => $log->getAddon(),
                    'download_count' => 0,
                    'logs' => []
                ];
            }
            
            $groups[$addonId]['download_count']++;
            $groups[$addonId]['logs'][] = $log;
        }
        
        return $groups;
    }
    
    /**
     * Získá statistiky stažení podle hodin
     */
    public function getHourlyStatistics(): array
    {
        $stats = array_fill(0, 24, 0);
        
        foreach ($this as $log) {
            $hour = (int)$log->getCreatedAt()->format('H');
            $stats[$hour]++;
        }
        
        return $stats;
    }
    
    /**
     * Získá statistiky podle dnů v týdnu
     */
    public function getDailyStatistics(): array
    {
        $stats = array_fill(1, 7, 0); // 1=Monday, 7=Sunday
        
        foreach ($this as $log) {
            $dayOfWeek = (int)$log->getCreatedAt()->format('N');
            $stats[$dayOfWeek]++;
        }
        
        return $stats;
    }
    
    /**
     * Získá unikátní IP adresy
     */
    public function getUniqueIpAddresses(): array
    {
        return array_unique($this->map(function(DownloadLog $log): ?string {
            return $log->getIpAddress();
        }));
    }
}
