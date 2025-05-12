<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\UserForm;

class UserFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @return UserForm
     */
    public function create(): UserForm
    {
        return new UserForm();
    }
}