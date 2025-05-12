<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\AddonTag;
use App\Factory\Interface\IAddonTagFactory;

/**
 * Továrna pro vytváření vazeb mezi doplňky a tagy
 * 
 * @implements IFactory<AddonTag>
 */
class AddonTagFactory implements IAddonTagFactory
{
    /**
     * Vytvoří novou instanci vazby doplňku a tagu
     * 
     * @param array $data
     * @return AddonTag
     */
    public function create(array $data): AddonTag
    {
        // Zajištění povinných polí
        if (!isset($data['addon_id'])) {
            throw new \InvalidArgumentException('Addon ID is required');
        }

        if (!isset($data['tag_id'])) {
            throw new \InvalidArgumentException('Tag ID is required');
        }
        
        return AddonTag::fromArray($data);
    }

    /**
     * Vytvoří vazbu mezi doplňkem a tagem
     * 
     * @param int $addonId ID doplňku
     * @param int $tagId ID tagu
     * @return AddonTag
     */
    public function createLink(int $addonId, int $tagId): AddonTag
    {
        return $this->create([
            'addon_id' => $addonId,
            'tag_id' => $tagId
        ]);
    }

    /**
     * Vytvoří vazby mezi doplňkem a více tagy
     * 
     * @param int $addonId ID doplňku
     * @param array $tagIds Pole ID tagů
     * @return array
     */
    public function createBatch(int $addonId, array $tagIds): array
    {
        $addonTags = [];
        
        foreach ($tagIds as $tagId) {
            $addonTags[] = $this->createLink($addonId, $tagId);
        }
        
        return $addonTags;
    }
}