<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[UniqueEntity(fields: ['slug'], message: 'Tento slug je již používán.')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Název tagu nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Název tagu musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Název tagu nesmí být delší než {{ limit }} znaků.'
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

    #[ORM\ManyToMany(targetEntity: Addon::class, mappedBy: 'tags')]
    private Collection $addons;

    public function __construct()
    {
        $this->addons = new ArrayCollection();
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
            $addon->addTag($this);
        }

        return $this;
    }

    public function removeAddon(Addon $addon): self
    {
        if ($this->addons->removeElement($addon)) {
            $addon->removeTag($this);
        }

        return $this;
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
        ];
    }
}