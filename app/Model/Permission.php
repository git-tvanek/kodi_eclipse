<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class Permission
{
    use SmartObject;

    public int $id;
    public string $name;
    public string $resource;
    public string $action;
    public ?string $description = null;

    /**
     * Create a Permission instance from array data
     */
    public static function fromArray(array $data): self
    {
        $permission = new self();
        
        if (isset($data['id'])) {
            $permission->id = (int) $data['id'];
        }
        
        $permission->name = $data['name'];
        $permission->resource = $data['resource'];
        $permission->action = $data['action'];
        $permission->description = $data['description'] ?? null;
        
        return $permission;
    }

    /**
     * Convert the Permission instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'resource' => $this->resource,
            'action' => $this->action,
            'description' => $this->description,
        ];
    }
}