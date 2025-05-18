<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Tag;
use App\Factory\Interface\ITagFactory;
use Nette\Utils\Strings;

/**
 * Továrna pro vytváření tagů
 * 
 * @implements IFactory<Tag>
 */
class TagFactory implements ITagFactory
{
    /**
     * Vytvoří novou instanci tagu
     * 
     * @param array $data
     * @return Tag
     */
    public function create(array $data): Tag
    {
        // Zajištění povinných polí
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Tag name is required');
        }

        // Automatické vytvoření slugu
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        
        return Tag::fromArray($data);
    }

    /**
     * Vytvoří tag pouze s názvem (slug se vygeneruje automaticky)
     * 
     * @param string $name Název tagu
     * @return Tag
     */
    public function createWithName(string $name): Tag
    {
        return $this->create([
            'name' => $name
        ]);
    }

    /**
     * Vytvoří tagy z pole názvů
     * 
     * @param array $names Pole názvů tagů
     * @return array
     */
    public function createBatch(array $names): array
    {
        $tags = [];
        
        foreach ($names as $name) {
            $tags[] = $this->createWithName($name);
        }
        
        return $tags;
    }
}