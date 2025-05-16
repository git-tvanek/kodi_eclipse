<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\ScreenshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotNull(message: 'Doplněk musí být vybrán.')]
    private Addon $addon;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'URL obrázku nesmí být prázdná.')]
    #[Assert\Url(message: 'URL obrázku musí být platná URL adresa.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'URL obrázku nesmí být delší než {{ limit }} znaků.'
    )]
    private string $url;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Popis nesmí být delší než {{ limit }} znaků.'
    )]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: 'Pořadí nemůže být záporné.'
    )]
    private int $sort_order = 0;

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

    /**
     * Konvertuje data entity do pole
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'addon_id' => $this->addon->getId(),
            'url' => $this->url,
            'description' => $this->description,
            'sort_order' => $this->sort_order
        ];
    }
}