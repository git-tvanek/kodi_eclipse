<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Tag;

/**
 * Rozhraní pro továrnu tagů
 * 
 * @extends IFactory<Tag>
 */
interface ITagFactory extends IFactory
{
    /**
     * Vytvoří novou instanci tagu
     * 
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag;
    
    /**
     * Vytvoří tag pouze s názvem (slug se vygeneruje automaticky)
     * 
     * @param string $name Název tagu
     * @return Tag
     */
    public function createWithName(string $name): Tag;
    
    /**
     * Vytvoří tagy z pole názvů
     * 
     * @param array $names Pole názvů tagů
     * @return array<Tag>
     */
    public function createBatch(array $names): array;
}