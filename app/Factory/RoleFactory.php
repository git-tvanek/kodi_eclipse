<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IRoleFactory;
use App\Model\Role;
use Nette\Utils\Strings;

class RoleFactory implements IRoleFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): Role
    {
        return Role::fromArray($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromName(string $name, ?string $code = null, ?string $description = null, int $priority = 0): Role
    {
        $role = new Role();
        $role->name = $name;
        $role->code = $code ?? Strings::webalize($name);
        $role->description = $description;
        $role->priority = $priority;
        
        return $role;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(Role $role, array $data): Role
    {
        $updatedRole = clone $role;
        
        if (isset($data['name'])) {
            $updatedRole->name = $data['name'];
        }
        
        if (isset($data['code'])) {
            $updatedRole->code = $data['code'];
        }
        
        if (isset($data['description'])) {
            $updatedRole->description = $data['description'];
        }
        
        if (isset($data['priority'])) {
            $updatedRole->priority = (int) $data['priority'];
        }
        
        return $updatedRole;
    }
}