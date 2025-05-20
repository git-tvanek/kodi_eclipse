<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\AddonReview;
use App\Factory\ReviewFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření recenzí
 * 
 * @template-extends EntityBuilder<AddonReview, ReviewFactory>
 * @implements IEntityBuilder<AddonReview>
 */
class ReviewBuilder extends EntityBuilder
{
    /**
     * @param ReviewFactory $factory
     */
    public function __construct(ReviewFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví ID doplňku
     * 
     * @param int $addonId
     * @return self
     */
    public function setAddonId(int $addonId): self
    {
        return $this->setValue('addon_id', $addonId);
    }
    
    /**
     * Nastaví ID uživatele
     * 
     * @param int|null $userId
     * @return self
     */
    public function setUserId(?int $userId): self
    {
        return $this->setValue('user_id', $userId);
    }
    
    /**
     * Nastaví jméno recenzenta (pro nepřihlášené uživatele)
     * 
     * @param string|null $name
     * @return self
     */
    public function setName(?string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví email recenzenta (pro nepřihlášené uživatele)
     * 
     * @param string|null $email
     * @return self
     */
    public function setEmail(?string $email): self
    {
        return $this->setValue('email', $email);
    }
    
    /**
     * Nastaví hodnocení
     * 
     * @param int $rating
     * @return self
     */
    public function setRating(int $rating): self
    {
        return $this->setValue('rating', $rating);
    }
    
    /**
     * Nastaví komentář
     * 
     * @param string|null $comment
     * @return self
     */
    public function setComment(?string $comment): self
    {
        return $this->setValue('comment', $comment);
    }
    
    /**
     * Nastaví příznak ověřené recenze
     * 
     * @param bool $isVerified
     * @return self
     */
    public function setIsVerified(bool $isVerified): self
    {
        return $this->setValue('is_verified', $isVerified);
    }
    
    /**
     * Nastaví příznak aktivní recenze
     * 
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        return $this->setValue('is_active', $isActive);
    }
    
    /**
     * Vytvoří recenzi od přihlášeného uživatele
     * 
     * @param int $addonId
     * @param int $userId
     * @param int $rating
     * @param string|null $comment
     * @return self
     */
    public function forUser(int $addonId, int $userId, int $rating, ?string $comment = null): self
    {
        $this->setValue('addon_id', $addonId);
        $this->setValue('user_id', $userId);
        $this->setValue('rating', $rating);
        return $this->setValue('comment', $comment);
    }
    
    /**
     * Vytvoří recenzi od nepřihlášeného uživatele
     * 
     * @param int $addonId
     * @param string $name
     * @param string|null $email
     * @param int $rating
     * @param string|null $comment
     * @return self
     */
    public function forGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): self
    {
        $this->setValue('addon_id', $addonId);
        $this->setValue('name', $name);
        $this->setValue('email', $email);
        $this->setValue('rating', $rating);
        return $this->setValue('comment', $comment);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): AddonReview
    {
        return $this->factory->createFromBuilder($this->data);
    }
}