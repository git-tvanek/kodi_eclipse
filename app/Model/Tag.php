<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class Tag
{
    use SmartObject;

    public int $id;
    public string $name;
    public string $slug;

    /**
     * Create a Tag instance from array data
     */
    public static function fromArray(array $data): self
    {
        $tag = new self();
        
        if (isset($data['id'])) {
            $tag->id = (int) $data['id'];
        }
        
        $tag->name = $data['name'];
        $tag->slug = $data['slug'];
        
        return $tag;
    }

    /**
     * Convert the Tag instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}