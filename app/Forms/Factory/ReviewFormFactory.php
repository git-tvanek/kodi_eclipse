<?php

declare(strict_types=1);

namespace App\Forms\Factory;

use App\Forms\ReviewForm;

class ReviewFormFactory
{
    /**
     * Vytvoří instanci formuláře
     * 
     * @param bool $userLoggedIn Je uživatel přihlášen
     * @param int|null $userId ID přihlášeného uživatele
     * @return ReviewForm
     */
    public function create(bool $userLoggedIn = false, ?int $userId = null): ReviewForm
    {
        return new ReviewForm($userLoggedIn, $userId);
    }
}