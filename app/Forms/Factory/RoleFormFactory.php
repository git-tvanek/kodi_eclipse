<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\RoleForm;

class RoleFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @return RoleForm
     */
    public function create(): RoleForm
    {
        return new RoleForm();
    }
}