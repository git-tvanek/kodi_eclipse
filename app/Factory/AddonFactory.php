<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Factory\Interface\IAddonFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Addon
 * 
 * @extends BaseFactory<Addon>
 * @implements IAddonFactory<Addon>
 */
class AddonFactory extends BaseFactory implements IAddonFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Addon::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Addon
    {
        /** @var Addon $addon */
        $addon = $this->createNewInstance();
        return $this->createFromExisting($addon, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Addon
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['slug'])) {
            $entity->setSlug($data['slug']);
        }
        
        if (isset($data['description'])) {
            $entity->setDescription($data['description']);
        }
        
        if (isset($data['version'])) {
            $entity->setVersion($data['version']);
        }
        
        if (isset($data['author_id'])) {
            /** @var Author $author */
            $author = $this->getReference(Author::class, (int)$data['author_id']);
            $entity->setAuthor($author);
        } elseif (isset($data['author']) && $data['author'] instanceof Author) {
            $entity->setAuthor($data['author']);
        }
        
        if (isset($data['category_id'])) {
            /** @var Category $category */
            $category = $this->getReference(Category::class, (int)$data['category_id']);
            $entity->setCategory($category);
        } elseif (isset($data['category']) && $data['category'] instanceof Category) {
            $entity->setCategory($data['category']);
        }
        
        if (isset($data['repository_url'])) {
            $entity->setRepositoryUrl($data['repository_url']);
        }
        
        if (isset($data['download_url'])) {
            $entity->setDownloadUrl($data['download_url']);
        }
        
        if (isset($data['icon_url'])) {
            $entity->setIconUrl($data['icon_url']);
        }
        
        if (isset($data['fanart_url'])) {
            $entity->setFanartUrl($data['fanart_url']);
        }
        
        if (isset($data['kodi_version_min'])) {
            $entity->setKodiVersionMin($data['kodi_version_min']);
        }
        
        if (isset($data['kodi_version_max'])) {
            $entity->setKodiVersionMax($data['kodi_version_max']);
        }
        
        if (isset($data['downloads_count'])) {
            $entity->setDownloadsCount((int)$data['downloads_count']);
        }
        
        if (isset($data['rating'])) {
            $entity->setRating((float)$data['rating']);
        }
        
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
            $entity->setCreatedAt($createdAt);
        } elseif ($isNew) {
            $entity->setCreatedAt(new DateTime());
        }
        
        if (isset($data['updated_at'])) {
            $updatedAt = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
            $entity->setUpdatedAt($updatedAt);
        } else {
            $entity->setUpdatedAt(new DateTime());
        }
        
        // Zpracování tagů
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tag) {
                if ($tag instanceof Tag) {
                    $entity->addTag($tag);
                } elseif (is_array($tag) && isset($tag['id'])) {
                    /** @var Tag $tagEntity */
                    $tagEntity = $this->getReference(Tag::class, (int)$tag['id']);
                    $entity->addTag($tagEntity);
                }
            }
        }
        
        return $entity;
    }
}