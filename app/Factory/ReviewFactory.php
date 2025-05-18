<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AddonReview;
use App\Factory\Interface\IReviewFactory;
use DateTime;

/**
 * Továrna pro vytváření recenzí
 * 
 * @implements IFactory<AddonReview>
 */
class ReviewFactory implements IReviewFactory
{
    /**
     * Vytvoří novou instanci recenze
     * 
     * @param array $data
     * @return AddonReview
     */
    public function create(array $data): AddonReview
    {
        // Zajištění povinných polí
        if (!isset($data['addon_id'])) {
            throw new \InvalidArgumentException('Addon ID is required');
        }

        if (!isset($data['rating'])) {
            throw new \InvalidArgumentException('Rating is required');
        }

        // Validace hodnocení
        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }

        // Výchozí hodnoty pro nepovinná pole
        $data['user_id'] = $data['user_id'] ?? null;
        $data['name'] = $data['name'] ?? null;
        $data['email'] = $data['email'] ?? null;
        $data['comment'] = $data['comment'] ?? null;
        $data['created_at'] = $data['created_at'] ?? new DateTime();
        
        return AddonReview::fromArray($data);
    }

    /**
     * Vytvoří recenzi od přihlášeného uživatele
     * 
     * @param int $addonId ID doplňku
     * @param int $userId ID uživatele
     * @param int $rating Hodnocení (1-5)
     * @param string|null $comment Komentář (volitelný)
     * @return AddonReview
     */
    public function createFromUser(int $addonId, int $userId, int $rating, ?string $comment = null): AddonReview
    {
        return $this->create([
            'addon_id' => $addonId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }

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
    public function createFromGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): AddonReview
    {
        return $this->create([
            'addon_id' => $addonId,
            'user_id' => null,
            'name' => $name,
            'email' => $email,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }
}