<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\Tag;
use App\Factory\TagFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Builder pro vytváření tagů
 * 
 * @template-extends EntityBuilder<Tag, TagFactory>
 * @implements IEntityBuilder<Tag>
 */
class TagBuilder extends EntityBuilder
{
    /**
     * @param TagFactory $factory
     */
    public function __construct(TagFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví název tagu
     * 
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setValue('name', $name);
    }
    
    /**
     * Nastaví slug tagu
     * 
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        return $this->setValue('slug', $slug);
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): Tag
    {
        return $this->factory->createFromBuilder($this->data);
    }
}