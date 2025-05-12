<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Model\AddonReview;

/**
 * Rozhraní pro továrnu recenzí
 * 
 * @extends IFactory<AddonReview>
 */
interface IReviewFactory extends IFactory
{
    /**
     * Vytvoří novou instanci recenze
     * 
     * @param array $data
     * @return AddonReview
     */
    public function create(array $data): AddonReview;
    
    /**
     * Vytvoří recenzi od přihlášeného uživatele
     * 
     * @param int $addonId ID doplňku
     * @param int $userId ID uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return AddonReview
     */
    public function createFromUser(int $addonId, int $userId, int $rating, ?string $comment = null): AddonReview;
    
    /**
     * Vytvoří recenzi od anonymního uživatele
     * 
     * @param int $addonId ID doplňku
     * @param string $name Jméno uživatele
     * @param string|null $email E-mail uživatele (volitelný)
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return AddonReview
     */
    public function createFromGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): AddonReview;
}