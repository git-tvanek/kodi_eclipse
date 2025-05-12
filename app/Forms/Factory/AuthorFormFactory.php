<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\AuthorForm;

class AuthorFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @return AuthorForm
     */
    public function create(): AuthorForm
    {
        return new AuthorForm();
    }
}