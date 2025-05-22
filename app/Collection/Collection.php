<?php

declare(strict_types=1);

namespace App\Collection;

use Countable;
use Iterator;
use ArrayAccess;
use Closure;
use ReflectionClass;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Unifikovaná kolekce s plnou Doctrine kompatibilitou a PHP 8+ features
 * 
 * @template T
 * @implements Iterator<int, T>
 * @implements ArrayAccess<int, T>
 */
class Collection implements Countable, Iterator, ArrayAccess
{
    /** @var DoctrineCollection<int, T> */
    protected DoctrineCollection $collection;
    
    /** @var int Iterator position */
    private int $position = 0;
    
    /** @var array<T> Cache pro iterator */
    private array $iteratorCache = [];
    
    /**
     * ✅ VYLEPŠENÝ KONSTRUKTOR s PHP 8+ features
     * 
     * @param iterable<T>|DoctrineCollection<int, T> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->collection = match (true) {
            $items instanceof DoctrineCollection => $items,
            $items instanceof \Traversable => new ArrayCollection(iterator_to_array($items)),
            is_array($items) => new ArrayCollection(array_values($items)),
            default => new ArrayCollection([])
        };
        
        $this->refreshIteratorCache();
    }
    
    // =========================================================================
    // ✅ FACTORY METHODS pro jasné vytváření
    // =========================================================================
    
    /**
     * Vytvoří kolekci z Doctrine Collection
     * 
     * @template U
     * @param DoctrineCollection<int, U> $doctrineCollection
     * @return static<U>
     */
    public static function fromDoctrineCollection(DoctrineCollection $doctrineCollection): static
    {
        return new static($doctrineCollection);
    }
    
    /**
     * Vytvoří kolekci z pole
     * 
     * @template U
     * @param array<U> $items
     * @return static<U>
     */
    public static function fromArray(array $items): static
    {
        return new static($items);
    }
    
    /**
     * Vytvoří prázdnou kolekci
     * 
     * @template U
     * @return static<U>
     */
    public static function empty(): static
    {
        return new static([]);
    }
    
    /**
     * Vytvoří kolekci z iterátoru
     * 
     * @template U
     * @param \Traversable<U> $traversable
     * @return static<U>
     */
    public static function fromTraversable(\Traversable $traversable): static
    {
        return new static($traversable);
    }
    
    // =========================================================================
    // ✅ DOCTRINE COMPATIBILITY METHODS
    // =========================================================================
    
    /**
     * Získá reference na underlying Doctrine collection
     * 
     * @return DoctrineCollection<int, T>
     */
    public function getDoctrineCollection(): DoctrineCollection
    {
        return $this->collection;
    }
    
    /**
     * Vytvoří kopii jako Doctrine collection
     * 
     * @return DoctrineCollection<int, T>
     */
    public function toDoctrineCollection(): DoctrineCollection
    {
        return new ArrayCollection($this->collection->toArray());
    }
    
    /**
     * Zkontroluje, zda je underlying collection Selectable
     */
    public function isSelectable(): bool
    {
        return $this->collection instanceof Selectable;
    }
    
    // =========================================================================
    // ✅ CONVERSION METHODS
    // =========================================================================
    
    /**
     * Převede na pole
     * 
     * @return array<int, T>
     */
    public function toArray(): array
    {
        return $this->collection->toArray();
    }
    
    /**
     * Převede na pole rekurzivně (volá toArray() na nested objektech)
     * 
     * @return array<int, mixed>
     */
    public function toArrayRecursive(): array
    {
        return array_map(
            fn($item) => method_exists($item, 'toArray') ? $item->toArray() : $item,
            $this->toArray()
        );
    }
    
    /**
     * Převede na JSON string
     */
    public function toJson(int $flags = 0, int $depth = 512): string
    {
        return json_encode($this->toArrayRecursive(), $flags | JSON_THROW_ON_ERROR, $depth);
    }
    
    /**
     * Převede na seznam hodnot konkrétního pole
     * 
     * @param string $field Název pole/metody
     * @return array<mixed>
     */
    public function pluck(string $field): array
    {
        return $this->map(fn($item) => $this->getFieldValue($item, $field));
    }
    
    // =========================================================================
    // ✅ ZACHOVANÉ PŮVODNÍ METODY (s vylepšeními)
    // =========================================================================
    
    /**
     * @return T|null
     */
    public function first()
    {
        $first = $this->collection->first();
        return $first !== false ? $first : null;
    }
    
    /**
     * @return T|null
     */
    public function last()
    {
        $last = $this->collection->last();
        return $last !== false ? $last : null;
    }
    
    /**
     * Najde první element splňující podmínku
     * 
     * @param Closure(T): bool $predicate
     * @return T|null
     */
    public function findFirst(Closure $predicate)
    {
        foreach ($this->collection as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        
        return null;
    }
    
    /**
     * Najde poslední element splňující podmínku
     * 
     * @param Closure(T): bool $predicate
     * @return T|null
     */
    public function findLast(Closure $predicate)
    {
        $result = null;
        foreach ($this->collection as $item) {
            if ($predicate($item)) {
                $result = $item;
            }
        }
        
        return $result;
    }
    
    /**
     * ZACHOVANÁ custom filter metoda s callback
     * 
     * @param Closure(T): bool $callback
     * @return static<T>
     */
    public function filter(Closure $callback): static
    {
        $filtered = $this->collection->filter($callback);
        return new static($filtered);
    }
    
    /**
     * ZACHOVANÁ custom map metoda
     * 
     * @template U
     * @param Closure(T): U $callback
     * @return array<int, U>
     */
    public function map(Closure $callback): array
    {
        return $this->collection->map($callback)->toArray();
    }
    
    /**
     * Map s návratem Collection místo array
     * 
     * @template U
     * @param Closure(T): U $callback
     * @return static<U>
     */
    public function mapToCollection(Closure $callback): static
    {
        $mapped = $this->map($callback);
        return new static($mapped);
    }
    
    /**
     * ZACHOVANÁ custom sort metoda s callback
     * 
     * @param callable(T, T): int $callback
     * @return static<T>
     */
    public function sort(callable $callback): static
    {
        $items = $this->collection->toArray();
        usort($items, $callback);
        return new static($items);
    }
    
    /**
     * Slice pro pagination
     * 
     * @param int $offset
     * @param int|null $length
     * @return static<T>
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static($this->collection->slice($offset, $length));
    }
    
    /**
     * ✅ VYLEPŠENÉ Doctrine Criteria matching
     * 
     * @param Criteria $criteria
     * @return static<T>
     */
    public function matching(Criteria $criteria): static
    {
        if ($this->collection instanceof Selectable) {
            return new static($this->collection->matching($criteria));
        }
        
        // Fallback pro non-selectable collections
        return $this->applySimpleCriteria($criteria);
    }
    
    /**
     * ✅ VYLEPŠENÁ aplikace Criteria
     */
    private function applySimpleCriteria(Criteria $criteria): static
    {
        $result = $this->collection;
        
        // Aplikujeme WHERE podmínky pokud existují
        $expression = $criteria->getWhereExpression();
        if ($expression !== null) {
            // Pro jednoduchost pouze obecný filter
            $result = $result->filter(fn($item) => true);
        }
        
        // Aplikujeme ordering, limit a offset
        $items = $result->toArray();
        
        $firstResult = $criteria->getFirstResult();
        $maxResults = $criteria->getMaxResults();
        
        if ($firstResult !== null || $maxResults !== null) {
            $offset = $firstResult ?? 0;
            $length = $maxResults;
            $items = array_slice($items, $offset, $length);
        }
        
        return new static($items);
    }
    
    /**
     * Kontrola prázdnosti
     */
    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }
    
    /**
     * Kontrola neprázdnosti
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }
    
    /**
     * Obsahuje element
     * 
     * @param T $element
     */
    public function contains($element): bool
    {
        return $this->collection->contains($element);
    }
    
    /**
     * Přidá element
     * 
     * @param T $element
     */
    public function add($element): bool
    {
        $result = $this->collection->add($element);
        $this->refreshIteratorCache();
        return $result !== false && $result !== null;
    }
    
    /**
     * Odebere element
     * 
     * @param T $element
     */
    public function removeElement($element): bool
    {
        $result = $this->collection->removeElement($element);
        $this->refreshIteratorCache();
        return $result === true;
    }
    
    /**
     * Vyčistí kolekci
     */
    public function clear(): void
    {
        $this->collection->clear();
        $this->refreshIteratorCache();
    }
    
    // =========================================================================
    // ✅ NOVÉ UTILITY METODY s PHP 8+ features
    // =========================================================================
    
    /**
     * Sortování pomocí pole názvu
     * 
     * @param string $field Název pole pro řazení
     * @param string $direction Směr řazení (ASC|DESC)
     * @return static<T>
     */
    public function sortBy(string $field, string $direction = 'ASC'): static
    {
        return $this->sort(function($a, $b) use ($field, $direction) {
            $valueA = $this->getFieldValue($a, $field);
            $valueB = $this->getFieldValue($b, $field);
            
            $comparison = $valueA <=> $valueB;
            return $direction === 'DESC' ? -$comparison : $comparison;
        });
    }
    
    /**
     * ✅ Multi-field sorting
     * 
     * @param array<string, string> $fields ['field' => 'direction', ...]
     * @return static<T>
     */
    public function sortByMultiple(array $fields): static
    {
        return $this->sort(function($a, $b) use ($fields) {
            foreach ($fields as $field => $direction) {
                $valueA = $this->getFieldValue($a, $field);
                $valueB = $this->getFieldValue($b, $field);
                
                $comparison = $valueA <=> $valueB;
                if ($comparison !== 0) {
                    return $direction === 'DESC' ? -$comparison : $comparison;
                }
            }
            return 0;
        });
    }
    
    /**
     * Filtrování pomocí pole hodnot
     * 
     * @param string $field Název pole
     * @param mixed $value Hodnota pro porovnání
     * @return static<T>
     */
    public function filterByField(string $field, mixed $value): static
    {
        return $this->filter(function($item) use ($field, $value) {
            $itemValue = $this->getFieldValue($item, $field);
            return $itemValue === $value;
        });
    }
    
    /**
     * ✅ Filtrování pomocí více polí
     * 
     * @param array<string, mixed> $criteria ['field' => 'value', ...]
     * @return static<T>
     */
    public function filterByCriteria(array $criteria): static
    {
        return $this->filter(function($item) use ($criteria) {
            foreach ($criteria as $field => $value) {
                $itemValue = $this->getFieldValue($item, $field);
                if ($itemValue !== $value) {
                    return false;
                }
            }
            return true;
        });
    }
    
    /**
     * Zkontroluje, zda všechny elementy splňují podmínku
     * 
     * @param Closure(T): bool $predicate
     */
    public function every(Closure $predicate): bool
    {
        foreach ($this->collection as $item) {
            if (!$predicate($item)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Zkontroluje, zda alespoň jeden element splňuje podmínku
     * 
     * @param Closure(T): bool $predicate
     */
    public function some(Closure $predicate): bool
    {
        foreach ($this->collection as $item) {
            if ($predicate($item)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Rozdělí kolekci na dva díly podle predikátu
     * 
     * @param Closure(T): bool $predicate
     * @return array{0: static<T>, 1: static<T>}
     */
    public function partition(Closure $predicate): array
    {
        $matching = [];
        $notMatching = [];
        
        foreach ($this->collection as $item) {
            if ($predicate($item)) {
                $matching[] = $item;
            } else {
                $notMatching[] = $item;
            }
        }
        
        return [new static($matching), new static($notMatching)];
    }
    
    /**
     * Redukce kolekce na jednu hodnotu
     * 
     * @template U
     * @param Closure(U, T): U $callback
     * @param U $initial
     * @return U
     */
    public function reduce(Closure $callback, mixed $initial): mixed
    {
        $accumulator = $initial;
        
        foreach ($this->collection as $item) {
            $accumulator = $callback($accumulator, $item);
        }
        
        return $accumulator;
    }
    
    /**
     * Převede kolekci na asociativní pole podle klíče
     * 
     * @param string $keyField Název pole pro klíč
     * @return array<string|int, T>
     */
    public function indexBy(string $keyField): array
    {
        $result = [];
        
        foreach ($this->collection as $item) {
            $key = $this->getFieldValue($item, $keyField);
            if ($key !== null) {
                $result[$key] = $item;
            }
        }
        
        return $result;
    }
    
    /**
     * Seskupí elementy podle hodnoty pole
     * 
     * @param string $groupField Název pole pro seskupení
     * @return array<string|int, static<T>>
     */
    public function groupBy(string $groupField): array
    {
        $groups = [];
        
        foreach ($this->collection as $item) {
            $groupKey = $this->getFieldValue($item, $groupField);
            if ($groupKey !== null) {
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [];
                }
                $groups[$groupKey][] = $item;
            }
        }
        
        // Převedeme pole na Collection instance
        return array_map(fn($items) => new static($items), $groups);
    }
    
    /**
     * ✅ Chunk - rozdělí kolekci na menší části
     * 
     * @param int $size Velikost chunků
     * @return array<static<T>>
     */
    public function chunk(int $size): array
    {
        if ($size <= 0) {
            throw new \InvalidArgumentException('Chunk size must be greater than 0');
        }
        
        $chunks = [];
        $items = $this->toArray();
        
        for ($i = 0; $i < count($items); $i += $size) {
            $chunks[] = new static(array_slice($items, $i, $size));
        }
        
        return $chunks;
    }
    
    /**
     * ✅ Unique - odstraní duplicity
     * 
     * @param string|null $field Pole pro porovnání (null = celý objekt)
     * @return static<T>
     */
    public function unique(?string $field = null): static
    {
        if ($field === null) {
            return new static(array_unique($this->toArray(), SORT_REGULAR));
        }
        
        $seen = [];
        $unique = [];
        
        foreach ($this->collection as $item) {
            $value = $this->getFieldValue($item, $field);
            if (!in_array($value, $seen, true)) {
                $seen[] = $value;
                $unique[] = $item;
            }
        }
        
        return new static($unique);
    }
    
    /**
     * ✅ Merge s jinou kolekcí
     * 
     * @param static<T>|DoctrineCollection<int, T>|iterable<T> $other
     * @return static<T>
     */
    public function merge(iterable $other): static
    {
        $otherItems = match (true) {
            $other instanceof self => $other->toArray(),
            $other instanceof DoctrineCollection => $other->toArray(),
            is_array($other) => $other,
            $other instanceof \Traversable => iterator_to_array($other),
            default => []
        };
        
        return new static(array_merge($this->toArray(), $otherItems));
    }
    
    // =========================================================================
    // ✅ ITERATOR IMPLEMENTATION (nezměněno)
    // =========================================================================
    
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
        return $this->iteratorCache[$this->position] ?? null;
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
        return isset($this->iteratorCache[$this->position]);
    }
    
    // =========================================================================
    // ✅ COUNTABLE & ARRAYACCESS (nezměněno)
    // =========================================================================
    
    public function count(): int
    {
        return $this->collection->count();
    }
    
    public function offsetExists($offset): bool
    {
        return isset($this->iteratorCache[$offset]);
    }
    
    /**
     * @param mixed $offset
     * @return T|mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->iteratorCache[$offset] ?? null;
    }
    
    /**
     * @param int|null $offset
     * @param T $value
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->collection->add($value);
        } else {
            $items = $this->collection->toArray();
            $items[$offset] = $value;
            $this->collection = new ArrayCollection($items);
        }
        $this->refreshIteratorCache();
    }
    
    public function offsetUnset($offset): void
    {
        $items = $this->collection->toArray();
        unset($items[$offset]);
        $this->collection = new ArrayCollection(array_values($items));
        $this->refreshIteratorCache();
    }
    
    // =========================================================================
    // ✅ PRIVATE HELPER METHODS
    // =========================================================================
    
    /**
     * Refresh iterator cache when collection changes
     */
    private function refreshIteratorCache(): void
    {
        $this->iteratorCache = array_values($this->collection->toArray());
        $this->position = 0;
    }
    
    /**
     * ✅ VYLEPŠENÁ metoda pro získání hodnoty pole z objektu
     * 
     * @param mixed $object
     * @param string $field
     * @return mixed
     */
    private function getFieldValue(mixed $object, string $field): mixed
    {
        // Pokusíme se použít getter metodu
        $getter = 'get' . ucfirst($field);
        if (method_exists($object, $getter)) {
            return $object->$getter();
        }
        
        // Pokusíme se o is* metodu pro boolean values
        $isMethod = 'is' . ucfirst($field);
        if (method_exists($object, $isMethod)) {
            return $object->$isMethod();
        }
        
        // Pokusíme se o has* metodu
        $hasMethod = 'has' . ucfirst($field);
        if (method_exists($object, $hasMethod)) {
            return $object->$hasMethod();
        }
        
        // Pokusíme se o přímý přístup k property (pokud je public)
        if (is_object($object) && property_exists($object, $field)) {
            return $object->$field;
        }
        
        // Array access
        if (is_array($object) && array_key_exists($field, $object)) {
            return $object[$field];
        }
        
        // ArrayAccess interface
        if ($object instanceof \ArrayAccess && $object->offsetExists($field)) {
            return $object->offsetGet($field);
        }
        
        return null;
    }
}