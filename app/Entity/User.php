<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['username'], message: 'Toto uživatelské jméno je již používáno.')]
#[UniqueEntity(fields: ['email'], message: 'Tento email je již používán.')]
class User implements IIdentity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Uživatelské jméno nesmí být prázdné.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Uživatelské jméno musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Uživatelské jméno nesmí být delší než {{ limit }} znaků.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_.-]+$/',
        message: 'Uživatelské jméno může obsahovat pouze písmena, čísla, podtržítka, tečky a pomlčky.'
    )]
    private string $username;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email nesmí být prázdný.')]
    #[Assert\Email(message: 'Email musí být platná emailová adresa.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email nesmí být delší než {{ limit }} znaků.'
    )]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Heslo nesmí být prázdné.')]
    private string $password_hash;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $is_verified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $verification_token = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $password_reset_token = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $password_reset_expires = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(message: 'URL profilového obrázku musí být platná URL adresa.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'URL profilového obrázku nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $profile_image = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum vytvoření nesmí být prázdné.')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum aktualizace nesmí být prázdné.')]
    private DateTime $updated_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $last_login = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AddonReview::class)]
    private Collection $reviews;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_roles')]
    private Collection $roles;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    /**
     * Implementace IIdentity - vrací ID uživatele
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Implementace IIdentity - vrací role uživatele
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles->map(function (Role $role) {
            return $role->getCode();
        })->toArray();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function setPasswordHash(string $password_hash): self
    {
        $this->password_hash = $password_hash;
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

    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verification_token;
    }

    public function setVerificationToken(?string $verification_token): self
    {
        $this->verification_token = $verification_token;
        return $this;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->password_reset_token;
    }

    public function setPasswordResetToken(?string $password_reset_token): self
    {
        $this->password_reset_token = $password_reset_token;
        return $this;
    }

    public function getPasswordResetExpires(): ?DateTime
    {
        return $this->password_reset_expires;
    }

    public function setPasswordResetExpires(?DateTime $password_reset_expires): self
    {
        $this->password_reset_expires = $password_reset_expires;
        return $this;
    }

    public function getProfileImage(): ?string
    {
        return $this->profile_image;
    }

    public function setProfileImage(?string $profile_image): self
    {
        $this->profile_image = $profile_image;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?DateTime $last_login): self
    {
        $this->last_login = $last_login;
        return $this;
    }

    /**
     * @return Collection<int, AddonReview>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(AddonReview $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(AddonReview $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getUser() === $this) {
                $review->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    public function verifyPassword(string $password): bool
    {
        return (new Passwords())->verify($password, $this->password_hash);
    }

    public static function hashPassword(string $password): string
    {
        return (new Passwords())->hash($password);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated_at = new DateTime();
    }

    /**
     * Pro získání dat kompatibilních s Nette\Security\SimpleIdentity
     */
    public function getIdentityData(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'profile_image' => $this->profile_image,
        ];
    }

    /**
     * Konvertuje data entity do pole
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'profile_image' => $this->profile_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login' => $this->last_login
        ];
    }
}