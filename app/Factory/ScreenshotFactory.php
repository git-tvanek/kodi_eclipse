<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Screenshot;
use App\Factory\Interface\IScreenshotFactory;

/**
 * Továrna pro vytváření screenshotů
 * 
 * @implements IFactory<Screenshot>
 */
class ScreenshotFactory implements IScreenshotFactory
{
    /**
     * Vytvoří novou instanci screenshotu
     * 
     * @param array $data
     * @return Screenshot
     */
    public function create(array $data): Screenshot
    {
        // Zajištění povinných polí
        if (!isset($data['addon_id'])) {
            throw new \InvalidArgumentException('Addon ID is required');
        }

        if (!isset($data['url'])) {
            throw new \InvalidArgumentException('URL is required');
        }

        // Výchozí hodnoty pro nepovinná pole
        $data['description'] = $data['description'] ?? null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        return Screenshot::fromArray($data);
    }

    /**
     * Vytvoří screenshot s popisem
     * 
     * @param int $addonId ID doplňku
     * @param string $url URL obrázku
     * @param string|null $description Popis obrázku
     * @param int $sortOrder Pořadí řazení
     * @return Screenshot
     */
    public function createWithDescription(int $addonId, string $url, ?string $description = null, int $sortOrder = 0): Screenshot
    {
        return $this->create([
            'addon_id' => $addonId,
            'url' => $url,
            'description' => $description,
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Vytvoří kolekci screenshotů z pole URL
     * 
     * @param int $addonId ID doplňku
     * @param array $urls Pole URL obrázků
     * @return array
     */
    public function createBatch(int $addonId, array $urls): array
    {
        $screenshots = [];
        $index = 0;
        
        foreach ($urls as $url) {
            $screenshots[] = $this->create([
                'addon_id' => $addonId,
                'url' => $url,
                'sort_order' => $index++
            ]);
        }
        
        return $screenshots;
    }
}