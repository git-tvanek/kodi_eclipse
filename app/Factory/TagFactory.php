<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Tag;
use App\Factory\Interface\ITagFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Tag
 * 
 * @extends BaseFactory<Tag>
 * @implements ITagFactory<Tag>
 */
class TagFactory extends BaseFactory implements ITagFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Tag::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Tag
    {
        /** @var Tag $tag */
        $tag = $this->createNewInstance();
        return $this->createFromExisting($tag, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Tag
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['slug'])) {
            $entity->setSlug($data['slug']);
        }
        
        return $entity;
    }
}