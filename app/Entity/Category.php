<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CategoryRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'categories')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['slug'], message: 'Tento slug je již používán.')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Název kategorie nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Název kategorie musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Název kategorie nesmí být delší než {{ limit }} znaků.'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Slug nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Slug musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Slug nesmí být delší než {{ limit }} znaků.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9-]+$/',
        message: 'Slug může obsahovat pouze malá písmena, čísla a pomlčky.'
    )]
    private string $slug;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Addon::class)]
    private Collection $addons;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum vytvoření nesmí být prázdné.')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum aktualizace nesmí být prázdné.')]
    private DateTime $updated_at;

    /**
     * Flag pro soft delete - když je true, kategorie je považována za smazanou
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $is_deleted = false;

    /**
     * Datum, kdy byla kategorie smazána (soft delete)
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $deleted_at = null;

    /**
     * Důvod smazání
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $deletion_reason = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
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
     * @return Collection<int, Category>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // Nastavení parent na null je zde v pořádku, protože parent může být null u Category
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

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
            $addon->setCategory($this);
        }

        return $this;
    }

    /**
     * Opravená metoda removeAddon - neporušuje integritu dat
     */
    public function removeAddon(Addon $addon): self
    {
        $this->addons->removeElement($addon);
        return $this;
    }

    /**
     * Vrací informaci, zda je kategorie smazaná (soft delete)
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    /**
     * Nastaví příznak smazané kategorie (soft delete)
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
     * Vrací datum smazání kategorie
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deleted_at;
    }

    /**
     * Vrací důvod smazání kategorie
     */
    public function getDeletionReason(): ?string
    {
        return $this->deletion_reason;
    }

    /**
     * Nastaví důvod smazání kategorie
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
            'slug' => $this->slug,
            'parent_id' => $this->parent ? $this->parent->getId() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_deleted' => $this->is_deleted,
            'deleted_at' => $this->deleted_at,
            'deletion_reason' => $this->deletion_reason,
        ];
    }

    /**
     * Vrací počet doplňků v kategorii
     */
    public function getAddonCount(): int
    {
        return $this->addons->count();
    }

    /**
     * Vrací počet podkategorií
     */
    public function getChildrenCount(): int
    {
        return $this->children->count();
    }

    /**
     * Kontroluje, zda kategorie může být bezpečně smazána (nemá doplňky ani podkategorie)
     */
    public function canBeHardDeleted(): bool
    {
        return $this->addons->isEmpty() && $this->children->isEmpty();
    }

    /**
     * Kontroluje, zda je kategorie obecná/výchozí
     */
    public function isGeneral(): bool
    {
        return $this->name === 'Obecné' && $this->slug === 'obecne';
    }

    /**
     * Vrací plnou cestu kategorie (včetně nadřazených kategorií)
     */
    public function getFullPath(): string
    {
        $path = $this->name;
        $current = $this->parent;
        
        while ($current !== null) {
            $path = $current->getName() . ' > ' . $path;
            $current = $current->getParent();
        }
        
        return $path;
    }

    /**
     * Vrací všechny podkategorie (rekurzivně)
     */
    public function getAllChildren(): array
    {
        $allChildren = [];
        
        foreach ($this->children as $child) {
            $allChildren[] = $child;
            $allChildren = array_merge($allChildren, $child->getAllChildren());
        }
        
        return $allChildren;
    }

    /**
     * Kontroluje, zda kategorie obsahuje danou podkategorii (přímo nebo nepřímo)
     */
    public function containsChild(self $category): bool
    {
        if ($this->children->contains($category)) {
            return true;
        }
        
        foreach ($this->children as $child) {
            if ($child->containsChild($category)) {
                return true;
            }
        }
        
        return false;
    }
}