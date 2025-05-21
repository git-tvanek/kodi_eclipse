<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\AddonReview;
use App\Entity\User;
use App\Factory\Interface\IAddonReviewFactory;
use DateTimeImmutable;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy AddonReview
 * 
 * @extends BaseFactory<AddonReview>
 * @implements IAddonReviewFactory<AddonReview>
 */
class AddonReviewFactory extends BaseFactory implements IAddonReviewFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, AddonReview::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): AddonReview
    {
        /** @var AddonReview $review */
        $review = $this->createNewInstance();
        return $this->createFromExisting($review, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): AddonReview
    {
        if (isset($data['addon_id'])) {
            /** @var Addon $addon */
            $addon = $this->getReference(Addon::class, (int)$data['addon_id']);
            $entity->setAddon($addon);
        } elseif (isset($data['addon']) && $data['addon'] instanceof Addon) {
            $entity->setAddon($data['addon']);
        }
        
        if (isset($data['user_id'])) {
            /** @var User $user */
            $user = $this->getReference(User::class, (int)$data['user_id']);
            $entity->setUser($user);
        } elseif (isset($data['user']) && $data['user'] instanceof User) {
            $entity->setUser($data['user']);
        }
        
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['email'])) {
            $entity->setEmail($data['email']);
        }
        
        if (isset($data['rating'])) {
            $entity->setRating((int)$data['rating']);
        }
        
        if (isset($data['comment'])) {
            $entity->setComment($data['comment']);
        }
        
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'] instanceof DateTimeImmutable 
                ? $data['created_at'] 
                : new DateTimeImmutable($data['created_at']);
            $entity->setCreatedAt($createdAt);
        }
        
        if (isset($data['updated_at'])) {
            $updatedAt = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
            $entity->setUpdatedAt($updatedAt);
        }
        
        if (isset($data['is_verified'])) {
            $entity->setIsVerified((bool)$data['is_verified']);
        }
        
        if (isset($data['is_active'])) {
            $entity->setIsActive((bool)$data['is_active']);
        }
        
        return $entity;
    }
}