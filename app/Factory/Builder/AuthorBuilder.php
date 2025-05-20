<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Author;
use App\Factory\AuthorFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření autorů
 * 
 * @template-extends EntityBuilder<Author, AuthorFactory>
 * @implements IEntityBuilder<Author>
 */
class AuthorBuilder extends EntityBuilder
{
    /**
     * @param AuthorFactory $factory
     */
    public function __construct(AuthorFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví jméno autora
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví email autora
     * 
     * @param string|null $email
     * @return self
     */
    public function setEmail(?string $email): self
    {
        return $this->setValue('email', $email);
    }
    
    /**
     * Nastaví webovou stránku autora
     * 
     * @param string|null $website
     * @return self
     */
    public function setWebsite(?string $website): self
    {
        return $this->setValue('website', $website);
    }
    
    /**
     * Nastaví příznak smazání autora
     * 
     * @param bool $isDeleted
     * @return self
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        return $this->setValue('is_deleted', $isDeleted);
    }
    
    /**
     * Nastaví důvod smazání autora
     * 
     * @param string|null $reason
     * @return self
     */
    public function setDeletionReason(?string $reason): self
    {
        return $this->setValue('deletion_reason', $reason);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Author
    {
        return $this->factory->createFromBuilder($this->data);
    }
}