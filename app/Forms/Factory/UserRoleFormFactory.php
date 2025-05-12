<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\UserRoleForm;
use App\Service\IRoleService;

class UserRoleFormFactory
{
    /** @var IRoleService */
    private IRoleService $roleService;
    
    /**
     * Konstruktor
     * 
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    
    /**
     * Vytvoří instanci formuláře
     * 
     * @return UserRoleForm
     */
    public function create(): UserRoleForm
    {
        return new UserRoleForm($this->roleService);
    }
}