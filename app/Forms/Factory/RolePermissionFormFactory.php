<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\RolePermissionForm;
use App\Service\IPermissionService;

class RolePermissionFormFactory
{
    /** @var IPermissionService */
    private IPermissionService $permissionService;
    
    /**
     * Konstruktor
     * 
     * @param IPermissionService $permissionService
     */
    public function __construct(IPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    
    /**
     * Vytvoří instanci formuláře
     * 
     * @return RolePermissionForm
     */
    public function create(): RolePermissionForm
    {
        return new RolePermissionForm($this->permissionService);
    }
}