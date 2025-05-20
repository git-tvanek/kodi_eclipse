<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření kategorií
 * 
 * @template-extends EntityBuilder<Category, CategoryFactory>
 * @implements IEntityBuilder<Category>
 */
class CategoryBuilder extends EntityBuilder
{
    /**
     * @param CategoryFactory $factory
     */
    public function __construct(CategoryFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví název kategorie
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví slug kategorie
     * 
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        return $this->setValue('slug', $slug);
    }
    
    /**
     * Nastaví ID nadřazené kategorie
     * 
     * @param int|null $parentId
     * @return self
     */
    public function setParentId(?int $parentId): self
    {
        return $this->setValue('parent_id', $parentId);
    }
    
    /**
     * Nastaví příznak smazání kategorie
     * 
     * @param bool $isDeleted
     * @return self
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        return $this->setValue('is_deleted', $isDeleted);
    }
    
    /**
     * Nastaví důvod smazání kategorie
     * 
     * @param string|null $reason
     * @return self
     */
    public function setDeletionReason(?string $reason): self
    {
        return $this->setValue('deletion_reason', $reason);
    }
    
    /**
     * Vytvoří kořenovou kategorii
     * 
     * @return self
     */
    public function asRootCategory(): self
    {
        return $this->setValue('parent_id', null);
    }
    
    /**
     * Vytvoří podkategorii
     * 
     * @param int $parentId
     * @return self
     */
    public function asSubcategory(int $parentId): self
    {
        return $this->setValue('parent_id', $parentId);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Category
    {
        return $this->factory->createFromBuilder($this->data);
    }
}