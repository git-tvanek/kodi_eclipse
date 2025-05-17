<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuthorRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\Table(name: 'authors')]
#[ORM\HasLifecycleCallbacks]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Jméno autora nesmí být prázdné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Jméno autora musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Jméno autora nesmí být delší než {{ limit }} znaků.'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email(message: 'Email musí být platná emailová adresa.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url(message: 'Webová stránka musí být platná URL adresa.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Webová stránka nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $website = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum vytvoření nesmí být prázdné.')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum aktualizace nesmí být prázdné.')]
    private DateTime $updated_at;

    /**
     * Flag pro soft delete - když je true, autor je považován za smazaného
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $is_deleted = false;

    /**
     * Datum, kdy byl autor smazán (soft delete)
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $deleted_at = null;

    /**
     * Důvod smazání
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $deletion_reason = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Addon::class)]
    private Collection $addons;

    public function __construct()
    {
        $this->addons = new ArrayCollection();
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;
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

    /**
     * @return Collection<int, Addon>
     */
    public function getAddons(): Collection
    {
        return $this->addons;
    }

    public function addAddon(Addon $addon): self
    {
        if (!$this->addons->contains($addon)) {
            $this->addons[] = $addon;
            $addon->setAuthor($this);
        }

        return $this;
    }

    public function removeAddon(Addon $addon): self
    {
        $this->addons->removeElement($addon);
        return $this;
    }

    /**
     * Vrací informaci, zda je autor smazaný (soft delete)
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    /**
     * Nastaví příznak smazaného autora (soft delete)
     */
    public function setIsDeleted(bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;
        
        if ($is_deleted) {
            $this->deleted_at = new DateTime();
        } else {
            $this->deleted_at = null;
            $this->deletion_reason = null;
        }
        
        return $this;
    }

    /**
     * Vrací datum smazání autora
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deleted_at;
    }

    /**
     * Vrací důvod smazání autora
     */
    public function getDeletionReason(): ?string
    {
        return $this->deletion_reason;
    }

    /**
     * Nastaví důvod smazání autora
     */
    public function setDeletionReason(?string $reason): self
    {
        $this->deletion_reason = $reason;
        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated_at = new DateTime();
    }

    /**
     * Konvertuje data entity do pole
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'website' => $this->website,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_deleted' => $this->is_deleted,
            'deleted_at' => $this->deleted_at,
            'deletion_reason' => $this->deletion_reason,
        ];
    }
    
    /**
     * Vrací počet doplňků, které autor má
     */
    public function getAddonCount(): int
    {
        return $this->addons->count();
    }
    
    /**
     * Kontroluje, zda autor může být bezpečně smazán (nemá doplňky)
     */
    public function canBeHardDeleted(): bool
    {
        return $this->addons->isEmpty();
    }
    
    /**
     * Kontroluje, zda je autor anonymní systémový autor
     */
    public function isAnonymous(): bool
    {
        return $this->name === 'Anonymní autor';
    }
}