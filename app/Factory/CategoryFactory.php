<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Category;
use App\Factory\Interface\ICategoryFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Category
 * 
 * @extends BaseFactory<Category>
 * @implements ICategoryFactory<Category>
 */
class CategoryFactory extends BaseFactory implements ICategoryFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Category::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Category
    {
        /** @var Category $category */
        $category = $this->createNewInstance();
        return $this->createFromExisting($category, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Category
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['slug'])) {
            $entity->setSlug($data['slug']);
        }
        
        if (isset($data['parent_id'])) {
            /** @var Category $parent */
            $parent = $this->getReference(Category::class, (int)$data['parent_id']);
            $entity->setParent($parent);
        } elseif (isset($data['parent']) && $data['parent'] instanceof Category) {
            $entity->setParent($data['parent']);
        } elseif (array_key_exists('parent', $data) && $data['parent'] === null) {
            $entity->setParent(null);
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
        
        if (isset($data['is_deleted'])) {
            $entity->setIsDeleted((bool)$data['is_deleted']);
        }
        
        if (isset($data['deletion_reason'])) {
            $entity->setDeletionReason($data['deletion_reason']);
        }
        
        return $entity;
    }
}