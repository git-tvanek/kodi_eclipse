<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\DownloadLog;

/**
 * Rozhraní pro továrnu DownloadLogFactory
 * 
 * @template-extends IBaseFactory<DownloadLog>
 */
interface IDownloadLogFactory extends IBaseFactory
{
    /**
     * Vytvoří novou instanci záznamu o stažení z pole dat
     * 
     * @param array $data Data pro vytvoření záznamu
     * @return DownloadLog Vytvořená instance
     */
    public function create(array $data): DownloadLog;
    
    /**
     * Aktualizuje existující entitu záznamu o stažení
     * 
     * @param DownloadLog $entity Existující entita
     * @param array $data Nová data
     * @param bool $isNew Zda jde o novou entitu
     * @return DownloadLog Aktualizovaná instance
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): DownloadLog;
}