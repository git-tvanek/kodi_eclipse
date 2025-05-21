<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Author;
use App\Factory\Interface\IAuthorFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy Author
 * 
 * @extends BaseFactory<Author>
 * @implements IAuthorFactory<Author>
 */
class AuthorFactory extends BaseFactory implements IAuthorFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Author::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): Author
    {
        /** @var Author $author */
        $author = $this->createNewInstance();
        return $this->createFromExisting($author, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): Author
    {
        if (isset($data['name'])) {
            $entity->setName($data['name']);
        }
        
        if (isset($data['email'])) {
            $entity->setEmail($data['email']);
        }
        
        if (isset($data['website'])) {
            $entity->setWebsite($data['website']);
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