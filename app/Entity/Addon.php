<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\Doctrine\AddonRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddonRepository::class)]
#[ORM\Table(name: 'addons')]
class Addon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $version;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'addons')]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: false)]
    private Author $author;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'addons')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private Category $category;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $repository_url = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $download_url;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $icon_url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fanart_url = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $kodi_version_min = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $kodi_version_max = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $downloads_count = 0;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $rating = 0.00;

    #[ORM\Column(type: 'datetime')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updated_at;

    #[ORM\OneToMany(mappedBy: 'addon', targetEntity: AddonReview::class, cascade: ['remove'])]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'addon', targetEntity: Screenshot::class, cascade: ['persist', 'remove'])]
    private Collection $screenshots;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'addons')]
    #[ORM\JoinTable(name: 'addon_tags')]
    private Collection $tags;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->screenshots = new ArrayCollection();
        $this->tags = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getRepositoryUrl(): ?string
    {
        return $this->repository_url;
    }

    public function setRepositoryUrl(?string $repository_url): self
    {
        $this->repository_url = $repository_url;
        return $this;
    }

    public function getDownloadUrl(): string
    {
        return $this->download_url;
    }

    public function setDownloadUrl(string $download_url): self
    {
        $this->download_url = $download_url;
        return $this;
    }

    public function getIconUrl(): ?string
    {
        return $this->icon_url;
    }

    public function setIconUrl(?string $icon_url): self
    {
        $this->icon_url = $icon_url;
        return $this;
    }

    public function getFanartUrl(): ?string
    {
        return $this->fanart_url;
    }

    public function setFanartUrl(?string $fanart_url): self
    {
        $this->fanart_url = $fanart_url;
        return $this;
    }

    public function getKodiVersionMin(): ?string
    {
        return $this->kodi_version_min;
    }

    public function setKodiVersionMin(?string $kodi_version_min): self
    {
        $this->kodi_version_min = $kodi_version_min;
        return $this;
    }

    public function getKodiVersionMax(): ?string
    {
        return $this->kodi_version_max;
    }

    public function setKodiVersionMax(?string $kodi_version_max): self
    {
        $this->kodi_version_max = $kodi_version_max;
        return $this;
    }

    public function getDownloadsCount(): int
    {
        return $this->downloads_count;
    }

    public function setDownloadsCount(int $downloads_count): self
    {
        $this->downloads_count = $downloads_count;
        return $this;
    }

    public function incrementDownloadsCount(): self
    {
        $this->downloads_count++;
        return $this;
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): self
    {
        $this->rating = $rating;
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
            $review->setAddon($this);
        }

        return $this;
    }

    public function removeReview(AddonReview $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getAddon() === $this) {
                $review->setAddon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Screenshot>
     */
    public function getScreenshots(): Collection
    {
        return $this->screenshots;
    }

    public function addScreenshot(Screenshot $screenshot): self
    {
        if (!$this->screenshots->contains($screenshot)) {
            $this->screenshots[] = $screenshot;
            $screenshot->setAddon($this);
        }

        return $this;
    }

    public function removeScreenshot(Screenshot $screenshot): self
    {
        if ($this->screenshots->removeElement($screenshot)) {
            if ($screenshot->getAddon() === $this) {
                $screenshot->setAddon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
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
            'description' => $this->description,
            'version' => $this->version,
            'author_id' => $this->author->getId(),
            'category_id' => $this->category->getId(),
            'repository_url' => $this->repository_url,
            'download_url' => $this->download_url,
            'icon_url' => $this->icon_url,
            'fanart_url' => $this->fanart_url,
            'kodi_version_min' => $this->kodi_version_min,
            'kodi_version_max' => $this->kodi_version_max,
            'downloads_count' => $this->downloads_count,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}