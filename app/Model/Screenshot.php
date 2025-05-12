<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class Screenshot
{
    use SmartObject;

    public int $id;
    public int $addon_id;
    public string $url;
    public ?string $description;
    public int $sort_order = 0;

    /**
     * Create a Screenshot instance from array data
     */
    public static function fromArray(array $data): self
    {
        $screenshot = new self();
        
        if (isset($data['id'])) {
            $screenshot->id = (int) $data['id'];
        }
        
        $screenshot->addon_id = (int) $data['addon_id'];
        $screenshot->url = $data['url'];
        $screenshot->description = $data['description'] ?? null;
        $screenshot->sort_order = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        
        return $screenshot;
    }

    /**
     * Convert the Screenshot instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'addon_id' => $this->addon_id,
            'url' => $this->url,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ];
    }
}