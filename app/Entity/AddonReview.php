<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\AddonReviewRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddonReviewRepository::class)]
#[ORM\Table(name: 'addon_reviews')]
#[ORM\Index(columns: ['created_at'], name: 'idx_review_created_at')]
#[ORM\Index(columns: ['rating'], name: 'idx_review_rating')]
#[ORM\HasLifecycleCallbacks]
class AddonReview
{
    public const RATING_MIN = 1;
    public const RATING_MAX = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Addon::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'addon_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Addon $addon;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'integer')]
    private int $rating;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updated_at = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $is_verified = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    public function __construct()
    {
        $this->created_at = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAddon(): Addon
    {
        return $this->addon;
    }

    public function setAddon(?Addon $addon): self
    {
        if ($addon === null) {
            throw new \InvalidArgumentException('Addon nemůže být null');
        }
        $this->addon = $addon;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        if ($name !== null && mb_strlen($name) > 255) {
            throw new \InvalidArgumentException('Jméno může mít maximálně 255 znaků');
        }
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        if ($email !== null) {
            if (mb_strlen($email) > 255) {
                throw new \InvalidArgumentException('Email může mít maximálně 255 znaků');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Neplatný formát emailové adresy');
            }
        }
        $this->email = $email;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        // Zajištění validního rozsahu
        if ($rating < self::RATING_MIN) {
            $rating = self::RATING_MIN;
        } else if ($rating > self::RATING_MAX) {
            $rating = self::RATING_MAX;
        }
        
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTime $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Vrací informaci, zda recenze je od registrovaného uživatele
     */
    public function isFromRegisteredUser(): bool
    {
        return $this->user !== null;
    }

    /**
     * Vrací informaci, zda recenze je pozitivní (4-5)
     */
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    /**
     * Vrací informaci, zda recenze je neutrální (3)
     */
    public function isNeutral(): bool
    {
        return $this->rating === 3;
    }

    /**
     * Vrací informaci, zda recenze je negativní (1-2)
     */
    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    /**
     * Lifecycle callback před aktualizací
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updated_at = new DateTime();
    }

    /**
     * Získá jméno autora recenze (registrovaný uživatel nebo anonymní jméno)
     */
    public function getAuthorName(): string
    {
        if ($this->user !== null) {
            return $this->user->getUsername();
        }

        return $this->name ?? 'Anonymní uživatel';
    }

    /**
     * Konvertuje recenzi na array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'addon_id' => $this->addon->getId(),
            'user_id' => $this->user?->getId(),
            'name' => $this->name,
            'email' => $this->email,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_verified' => $this->is_verified,
            'is_active' => $this->is_active
        ];
    }
}