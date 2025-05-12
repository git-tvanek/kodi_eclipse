<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\TagForm;

class TagFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @return TagForm
     */
    public function create(): TagForm
    {
        return new TagForm();
    }
}