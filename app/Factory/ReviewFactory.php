<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\AddonReview;
use App\Entity\Addon;
use App\Entity\User;
use App\Factory\Interface\IReviewFactory;
use App\Factory\Builder\ReviewBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use DateTimeImmutable;

/**
 * Továrna pro vytváření recenzí
 * 
 * @template-extends BuilderFactory<AddonReview, ReviewBuilder>
 * @implements IReviewFactory
 */
class ReviewFactory extends BuilderFactory implements IReviewFactory
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?ValidatorInterface $validator = null
    ) {
        parent::__construct($entityManager, $validator);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return AddonReview::class;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createBuilder(): ReviewBuilder
    {
        return new ReviewBuilder($this);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'addon_id',
            'rating'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'user_id' => null,
            'name' => null,
            'email' => null,
            'comment' => null,
            'is_verified' => false,
            'is_active' => true
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function processBeforeCreate(array $data): array
    {
        // Validace hodnocení
        if (isset($data['rating'])) {
            $rating = (int)$data['rating'];
            if ($rating < AddonReview::RATING_MIN || $rating > AddonReview::RATING_MAX) {
                throw new \InvalidArgumentException('Rating must be between ' . AddonReview::RATING_MIN . ' and ' . AddonReview::RATING_MAX);
            }
        }
        
        // Převedení ID na reference entit
        if (isset($data['addon_id'])) {
            $data['addon'] = $this->entityManager->getReference(Addon::class, $data['addon_id']);
            unset($data['addon_id']);
        }
        
        if (isset($data['user_id'])) {
            $data['user'] = $this->entityManager->getReference(User::class, $data['user_id']);
            unset($data['user_id']);
        }
        
        // Nastavení data vytvoření
        if (!isset($data['created_at'])) {
            $data['created_at'] = new DateTimeImmutable();
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): AddonReview
    {
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(object $entity, array $overrideData = [], bool $createNew = true): AddonReview
    {
        return parent::createFromExisting($entity, $overrideData, $createNew);
    }
    
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function createFromGuest(int $addonId, string $name, ?string $email, int $rating, ?string $comment = null): AddonReview
    {
        return $this->create([
            'addon_id' => $addonId,
            'name' => $name,
            'email' => $email,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }
}