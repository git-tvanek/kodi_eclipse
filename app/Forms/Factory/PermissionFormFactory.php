<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\PermissionForm;

class PermissionFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @return PermissionForm
     */
    public function create(): PermissionForm
    {
        return new PermissionForm();
    }
}