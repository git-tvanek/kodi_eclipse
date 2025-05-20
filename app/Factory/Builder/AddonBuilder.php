<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Addon;
use App\Factory\AddonFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření doplňků
 * 
 * @template-extends EntityBuilder<Addon, AddonFactory>
 * @implements IEntityBuilder<Addon>
 */
class AddonBuilder extends EntityBuilder
{
    /**
     * @param AddonFactory $factory
     */
    public function __construct(AddonFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví název doplňku
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví slug doplňku
     * 
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        return $this->setValue('slug', $slug);
    }
    
    /**
     * Nastaví popis doplňku
     * 
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        return $this->setValue('description', $description);
    }
    
    /**
     * Nastaví verzi doplňku
     * 
     * @param string $version
     * @return self
     */
    public function setVersion(string $version): self
    {
        return $this->setValue('version', $version);
    }
    
    /**
     * Nastaví ID autora doplňku
     * 
     * @param int $authorId
     * @return self
     */
    public function setAuthorId(int $authorId): self
    {
        return $this->setValue('author_id', $authorId);
    }
    
    /**
     * Nastaví ID kategorie doplňku
     * 
     * @param int $categoryId
     * @return self
     */
    public function setCategoryId(int $categoryId): self
    {
        return $this->setValue('category_id', $categoryId);
    }
    
    /**
     * Nastaví URL repozitáře doplňku
     * 
     * @param string|null $repositoryUrl
     * @return self
     */
    public function setRepositoryUrl(?string $repositoryUrl): self
    {
        return $this->setValue('repository_url', $repositoryUrl);
    }
    
    /**
     * Nastaví URL pro stažení doplňku
     * 
     * @param string $downloadUrl
     * @return self
     */
    public function setDownloadUrl(string $downloadUrl): self
    {
        return $this->setValue('download_url', $downloadUrl);
    }
    
    /**
     * Nastaví URL ikony doplňku
     * 
     * @param string|null $iconUrl
     * @return self
     */
    public function setIconUrl(?string $iconUrl): self
    {
        return $this->setValue('icon_url', $iconUrl);
    }
    
    /**
     * Nastaví URL fanart obrázku doplňku
     * 
     * @param string|null $fanartUrl
     * @return self
     */
    public function setFanartUrl(?string $fanartUrl): self
    {
        return $this->setValue('fanart_url', $fanartUrl);
    }
    
    /**
     * Nastaví minimální verzi Kodi
     * 
     * @param string|null $kodiVersionMin
     * @return self
     */
    public function setKodiVersionMin(?string $kodiVersionMin): self
    {
        return $this->setValue('kodi_version_min', $kodiVersionMin);
    }
    
    /**
     * Nastaví maximální verzi Kodi
     * 
     * @param string|null $kodiVersionMax
     * @return self
     */
    public function setKodiVersionMax(?string $kodiVersionMax): self
    {
        return $this->setValue('kodi_version_max', $kodiVersionMax);
    }
    
    /**
     * Nastaví počet stažení doplňku
     * 
     * @param int $downloadsCount
     * @return self
     */
    public function setDownloadsCount(int $downloadsCount): self
    {
        return $this->setValue('downloads_count', $downloadsCount);
    }
    
    /**
     * Nastaví hodnocení doplňku
     * 
     * @param float $rating
     * @return self
     */
    public function setRating(float $rating): self
    {
        return $this->setValue('rating', $rating);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Addon
    {
        return $this->factory->createFromBuilder($this->data);
    }
}