<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Model\AddonTag;

/**
 * Rozhraní pro továrnu vazeb mezi doplňky a tagy
 * 
 * @extends IFactory<AddonTag>
 */
interface IAddonTagFactory extends IFactory
{
    /**
     * Vytvoří novou instanci vazby doplňku a tagu
     * 
     * @param array $data
     * @return AddonTag
     */
    public function create(array $data): AddonTag;
    
    /**
     * Vytvoří vazbu mezi doplňkem a tagem
     * 
     * @param int $addonId ID doplňku
     * @param int $tagId ID tagu
     * @return AddonTag
     */
    public function createLink(int $addonId, int $tagId): AddonTag;
    
    /**
     * Vytvoří vazby mezi doplňkem a více tagy
     * 
     * @param int $addonId ID doplňku
     * @param array $tagIds Pole ID tagů
     * @return array<AddonTag>
     */
    public function createBatch(int $addonId, array $tagIds): array;
}