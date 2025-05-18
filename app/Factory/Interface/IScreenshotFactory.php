<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Screenshot;

/**
 * Rozhraní pro továrnu screenshotů
 * 
 * @extends IFactory<Screenshot>
 */
interface IScreenshotFactory extends IFactory
{
    /**
     * Vytvoří novou instanci screenshotu
     * 
     * @param array $data
     * @return Screenshot
     */
    public function create(array $data): Screenshot;
    
    /**
     * Vytvoří screenshot s popisem
     * 
     * @param int $addonId ID doplňku
     * @param string $url URL obrázku
     * @param string|null $description Popis obrázku
     * @param int $sortOrder Pořadí řazení
     * @return Screenshot
     */
    public function createWithDescription(int $addonId, string $url, ?string $description = null, int $sortOrder = 0): Screenshot;
    
    /**
     * Vytvoří kolekci screenshotů z pole URL
     * 
     * @param int $addonId ID doplňku
     * @param array $urls Pole URL obrázků
     * @return array<Screenshot>
     */
    public function createBatch(int $addonId, array $urls): array;
}