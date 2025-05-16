<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permissions')]
#[UniqueEntity(
    fields: ['resource', 'action'],
    message: 'Tato kombinace zdroje a akce již existuje.'
)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Název práva nesmí být prázdný.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Název práva musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Název práva nesmí být delší než {{ limit }} znaků.'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Zdroj nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Zdroj musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Zdroj nesmí být delší než {{ limit }} znaků.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Zdroj může obsahovat pouze písmena, čísla a podtržítka.'
    )]
    private string $resource;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Akce nesmí být prázdná.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'Akce musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Akce nesmí být delší než {{ limit }} znaků.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Akce může obsahovat pouze písmena, čísla a podtržítka.'
    )]
    private string $action;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Popis nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: Role::class, mappedBy: 'permissions')]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
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

    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removePermission($this);
        }

        return $this;
    }

    /**
     * Získá identifikátor práva pro Nette Permission ve formátu "resource:action"
     */
    public function getNettePermissionId(): string
    {
        return "{$this->resource}:{$this->action}";
    }

    /**
     * Konvertuje data entity do pole
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'resource' => $this->resource,
            'action' => $this->action,
            'description' => $this->description,
        ];
    }
}