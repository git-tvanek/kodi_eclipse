<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class Category
{
    use SmartObject;

    public int $id;
    public string $name;
    public string $slug;
    public ?int $parent_id;

    /**
     * Create a Category instance from array data
     */
    public static function fromArray(array $data): self
    {
        $category = new self();
        
        if (isset($data['id'])) {
            $category->id = (int) $data['id'];
        }
        
        $category->name = $data['name'];
        $category->slug = $data['slug'];
        $category->parent_id = isset($data['parent_id']) ? (int) $data['parent_id'] : null;
        
        return $category;
    }

    /**
     * Convert the Category instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
        ];
    }
}