<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\RoleRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'roles')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['code'], message: 'Tento kód role je již používán.')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Název role nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Název role musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Název role nesmí být delší než {{ limit }} znaků.'
    )]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Kód role nesmí být prázdný.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Kód role musí mít alespoň {{ limit }} znaky.',
        maxMessage: 'Kód role nesmí být delší než {{ limit }} znaků.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Kód role může obsahovat pouze písmena, čísla a podtržítka.'
    )]
    private string $code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Popis nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Priorita nemůže být záporná.'
    )]
    private int $priority = 0;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum vytvoření nesmí být prázdné.')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: 'Datum aktualizace nesmí být prázdné.')]
    private DateTime $updated_at;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'roles')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'roles')]
    #[ORM\JoinTable(name: 'role_permissions')]
    private Collection $permissions;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
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

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
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
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeRole($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        $this->permissions->removeElement($permission);
        return $this;
    }

    /**
     * Vrací kód role pro použití v Nette Permission
     */
    public function getNetteRoleId(): string
    {
        return $this->code;
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
            'code' => $this->code,
            'description' => $this->description,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}