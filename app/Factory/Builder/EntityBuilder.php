<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Factory\Interface\IBaseFactory;
use App\Factory\Interface\IEntityBuilder;

/**
 * Základní implementace pro buildery entit
 * 
 * @template T of object
 * @template F of IBaseFactory
 * @implements IEntityBuilder<T>
 */
abstract class EntityBuilder implements IEntityBuilder
{
    /** @var array */
    protected array $data = [];
    
    /** @var F */
    protected $factory;
    
    /**
     * @param F $factory
     */
    public function __construct(object $factory)
    {
        $this->factory = $factory;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Nastaví hodnotu
     * 
     * @param string $key
     * @param mixed $value
     * @return self
     */
    protected function setValue(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    abstract public function build(): object;
}