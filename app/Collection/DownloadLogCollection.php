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

    // ========== BUSINESS LOGIC METODY ==========

    /**
     * 📈 Top doplňky podle stažení
     */
    public function getTopDownloadedAddons(int $limit = 10): array
    {
        $addonStats = [];
        
        foreach ($this as $log) {
            $addonId = $log->getAddon()->getId();
            if (!isset($addonStats[$addonId])) {
                $addonStats[$addonId] = [
                    'addon' => $log->getAddon(),
                    'download_count' => 0
                ];
            }
            $addonStats[$addonId]['download_count']++;
        }
        
        uasort($addonStats, function($a, $b) {
            return $b['download_count'] <=> $a['download_count'];
        });
        
        return array_slice(array_values($addonStats), 0, $limit);
    }

    /**
     * 🌍 Analýza podle IP adres
     */
    public function getIpAnalysis(): array
    {
        $ipStats = [];
        $uniqueIps = [];
        
        foreach ($this as $log) {
            $ip = $log->getIpAddress();
            if ($ip) {
                $uniqueIps[$ip] = true;
                if (!isset($ipStats[$ip])) {
                    $ipStats[$ip] = 0;
                }
                $ipStats[$ip]++;
            }
        }
        
        arsort($ipStats);
        
        return [
            'unique_ips' => count($uniqueIps),
            'total_downloads' => $this->count(),
            'top_ips' => array_slice($ipStats, 0, 10, true),
            'downloads_per_ip' => $this->count() > 0 ? round($this->count() / max(1, count($uniqueIps)), 2) : 0
        ];
    }

    /**
     * 📅 Analýza podle času
     */
    public function getTimeAnalysis(): array
    {
        $hourlyStats = array_fill(0, 24, 0);
        $dailyStats = array_fill(1, 7, 0); // 1=Monday, 7=Sunday
        $monthlyStats = [];
        
        foreach ($this as $log) {
            $createdAt = $log->getCreatedAt();
            
            // Hodiny
            $hour = (int)$createdAt->format('H');
            $hourlyStats[$hour]++;
            
            // Dny v týdnu
            $dayOfWeek = (int)$createdAt->format('N');
            $dailyStats[$dayOfWeek]++;
            
            // Měsíce
            $month = $createdAt->format('Y-m');
            if (!isset($monthlyStats[$month])) {
                $monthlyStats[$month] = 0;
            }
            $monthlyStats[$month]++;
        }
        
        return [
            'hourly' => $hourlyStats,
            'daily' => $dailyStats,
            'monthly' => $monthlyStats,
            'peak_hour' => array_search(max($hourlyStats), $hourlyStats),
            'peak_day' => array_search(max($dailyStats), $dailyStats)
        ];
    }

    /**
     * 🔥 Nedávné stažení
     */
    public function getRecentDownloads(int $hours = 24): self
    {
        $since = new \DateTime("-{$hours} hours");
        
        return $this->filter(function(DownloadLog $log) use ($since) {
            return $log->getCreatedAt() >= $since;
        });
    }

    /**
     * 📊 Rychlé statistiky
     */
    public function getQuickStats(): array
    {
        return [
            'total_downloads' => $this->count(),
            'unique_addons' => $this->unique(function($log) {
                return $log->getAddon()->getId();
            })->count(),
            'unique_ips' => $this->unique('ipAddress')->count(),
            'downloads_today' => $this->getRecentDownloads(24)->count(),
            'downloads_this_week' => $this->getRecentDownloads(168)->count() // 24*7
        ];
    }
}
