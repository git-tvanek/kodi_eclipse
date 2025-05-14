<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\ScreenshotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreenshotRepository::class)]
#[ORM\Table(name: 'screenshots')]
class Screenshot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Addon::class, inversedBy: 'screenshots')]
    #[ORM\JoinColumn(name: 'addon_id', referencedColumnName: 'id', nullable: false)]
    private Addon $addon;

    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sort_order = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAddon(): Addon
    {
        return $this->addon;
    }

    public function setAddon(Addon $addon): self
    {
        $this->addon = $addon;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
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

    public function getSortOrder(): int
    {
        return $this->sort_order;
    }

    public function setSortOrder(int $sort_order): self
    {
        $this->sort_order = $sort_order;
        return $this;
    }
}