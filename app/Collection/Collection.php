<?php

declare(strict_types=1);

namespace App\Collection;

use Countable;
use Iterator;
use ArrayAccess;

/**
 * Základní kolekce pro entity
 * 
 * @template T
 * @implements Iterator<int, T>
 * @implements ArrayAccess<int, T>
 */
class Collection implements Countable, Iterator, ArrayAccess
{
    /** @var array<int, T> */
    protected array $items = [];
    
    /** @var int */
    protected int $position = 0;
    
    /**
     * @param array<int, T> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }
    
    /**
     * @return T|null
     */
    public function first()
    {
        if (count($this->items) === 0) {
            return null;
        }
        
        return $this->items[0];
    }
    
    /**
     * @return T|null
     */
    public function last()
    {
        if (count($this->items) === 0) {
            return null;
        }
        
        return $this->items[count($this->items) - 1];
    }
    
    /**
     * @return array<int, T>
     */
    public function toArray(): array
    {
        return $this->items;
    }
    
    /**
     * @param callable(T): bool $callback
     * @return static
     */
    public function filter(callable $callback): self
    {
        return new static(array_values(array_filter($this->items, $callback)));
    }
    
    /**
     * @template U
     * @param callable(T): U $callback
     * @return array<int, U>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }
    
    /**
     * @param callable(T, T): int $callback
     * @return static
     */
    public function sort(callable $callback): self
    {
        $items = $this->items;
        usort($items, $callback);
        return new static($items);
    }
    
    /* Iterator methods */
    
    public function rewind(): void
    {
        $this->position = 0;
    }
    
    /**
     * @return T|mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->items[$this->position];
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function next(): void
    {
        ++$this->position;
    }
    
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
    
    /* Countable method */
    
    public function count(): int
    {
        return count($this->items);
    }
    
    /* ArrayAccess methods */
    
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }
    
    /**
     * @param mixed $offset
     * @return T|mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }
    
    /**
     * @param int|null $offset
     * @param T $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
        $this->items = array_values($this->items);
    }
}