<?php

declare(strict_types=1);

namespace App\Model;

use Nette\SmartObject;

class Role
{
    use SmartObject;

    public int $id;
    public string $name;
    public string $code;
    public ?string $description = null;
    public int $priority = 0;

    /**
     * Create a Role instance from array data
     */
    public static function fromArray(array $data): self
    {
        $role = new self();
        
        if (isset($data['id'])) {
            $role->id = (int) $data['id'];
        }
        
        $role->name = $data['name'];
        $role->code = $data['code'];
        $role->description = $data['description'] ?? null;
        $role->priority = (int) ($data['priority'] ?? 0);
        
        return $role;
    }

    /**
     * Convert the Role instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'priority' => $this->priority,
        ];
    }
    
    /**
     * Vrací kód role pro použití v Nette Permission
     * 
     * @return string
     */
    public function getNetteRoleId(): string
    {
        return $this->code;
    }
}