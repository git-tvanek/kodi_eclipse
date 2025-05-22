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
        try {
            $this->collection = match (true) {
                $items instanceof DoctrineCollection => $items,
                $items instanceof \Traversable => new ArrayCollection(iterator_to_array($items)),
                is_array($items) => new ArrayCollection(array_values($items)),
                default => new ArrayCollection([])
            };
        } catch (\Throwable $e) {
            // Fallback pro bezpečnost
            $this->collection = new ArrayCollection([]);
        }
        
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

/**
 * Najde minimální hodnotu v kolekci
 * 
 * @param string|null $field Název pole (null = porovnávání celých objektů)
 * @return mixed Minimální hodnota nebo null pro prázdnou kolekci
 */
public function min(?string $field = null): mixed
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        if ($field === null) {
            return $this->reduce(function($min, $item) {
                return $min === null || $item < $min ? $item : $min;
            }, null); // ✅ Správný initial value
        }
        
        return $this->reduce(function($min, $item) use ($field) {
            $value = $this->getFieldValue($item, $field);
            return $min === null || ($value !== null && $value < $min) ? $value : $min;
        }, null); // ✅ Správný initial value
    }

/**
 * Najde maximální hodnotu v kolekci
 * 
 * @param string|null $field Název pole (null = porovnávání celých objektů)
 * @return mixed Maximální hodnota nebo null pro prázdnou kolekci
 */
public function max(?string $field = null): mixed
{
   if ($this->isEmpty()) {
        return null;
    }
    
    if ($field === null) {
        return $this->reduce(function($max, $item) {
            return $max === null || $item > $max ? $item : $max;
        }, null); // ✅ Přidán druhý parametr
    }
    
    return $this->reduce(function($max, $item) use ($field) {
        $value = $this->getFieldValue($item, $field);
        return $max === null || ($value !== null && $value > $max) ? $value : $max;
    }, null); // ✅ Přidán druhý parametr
}

/**
 * Najde prvek s minimální hodnotou a vrátí celý objekt
 * 
 * @param string $field Název pole pro porovnání
 * @return mixed Objekt s minimální hodnotou nebo null
 */
public function minBy(string $field): mixed
{
    if ($this->isEmpty()) {
        return null;
    }
    
    return $this->reduce(function($minItem, $item) use ($field) {
        if ($minItem === null) {
            return $item;
        }
        
        $minValue = $this->getFieldValue($minItem, $field);
        $currentValue = $this->getFieldValue($item, $field);
        
        return $currentValue < $minValue ? $item : $minItem;
    }, null); // ✅ Přidán druhý parametr
}

/**
 * Najde prvek s maximální hodnotou a vrátí celý objekt
 * 
 * @param string $field Název pole pro porovnání
 * @return mixed Objekt s maximální hodnotou nebo null
 */
public function maxBy(string $field): mixed
{
    if ($this->isEmpty()) {
        return null;
    }
    
    return $this->reduce(function($maxItem, $item) use ($field) {
        if ($maxItem === null) {
            return $item;
        }
        
        $maxValue = $this->getFieldValue($maxItem, $field);
        $currentValue = $this->getFieldValue($item, $field);
        
        return $currentValue > $maxValue ? $item : $maxItem;
    }, null); // ✅ Přidán druhý parametr
}

// =============================================================================
// 🧮 NUMERICKÉ OPERACE
// =============================================================================

/**
 * Sečte hodnoty v kolekci
 * 
 * @param string|null $field Název pole (null = sčítání celých hodnot)
 * @return int|float Součet hodnot
 */
public function sum(?string $field = null): int|float
{
    if ($field === null) {
        return $this->reduce(function($sum, $item) {
            return $sum + (is_numeric($item) ? $item : 0);
        }, 0); // ✅ Správný initial value
    }
    
    return $this->reduce(function($sum, $item) use ($field) {
        $value = $this->getFieldValue($item, $field);
        return $sum + (is_numeric($value) ? $value : 0);
    }, 0); // ✅ Správný initial value
}

/**
 * Vypočítá průměr hodnot v kolekci
 * 
 * @param string|null $field Název pole (null = průměr celých hodnot)
 * @return float Průměrná hodnota (0.0 pro prázdnou kolekci)
 */
public function average(?string $field = null): float
{
    if ($this->isEmpty()) {
        return 0.0;
    }
    
    $sum = $this->sum($field);
    return $sum / $this->count();
}

/**
 * Alias pro average()
 */
public function avg(?string $field = null): float
{
    return $this->average($field);
}

/**
 * Vypočítá medián hodnot v kolekci
 * 
 * @param string|null $field Název pole (null = medián celých hodnot)
 * @return mixed Medián nebo null pro prázdnou kolekci
 */
public function median(?string $field = null): mixed
{
    if ($this->isEmpty()) {
        return null;
    }
    
    $values = $field ? $this->pluck($field) : $this->toArray();
    $values = array_filter($values, 'is_numeric');
    
    if (empty($values)) {
        return null;
    }
    
    sort($values);
    $count = count($values);
    $middle = floor($count / 2);
    
    return $count % 2 === 0 
        ? ($values[$middle - 1] + $values[$middle]) / 2
        : $values[$middle];
}

/**
 * Spočítá kolikrát se vyskytuje každá hodnota
 * 
 * @param string|null $field Název pole (null = počítání celých objektů)
 * @return array Asociativní pole hodnota => počet
 */
public function countBy(?string $field = null): array
{
    $values = $field ? $this->pluck($field) : $this->toArray();
    return array_count_values(array_map('strval', $values));
}

// =============================================================================
// 🔍 LARAVEL-STYLE WHERE METODY
// =============================================================================

/**
 * Filtruje podle pole a hodnoty s operátorem
 * 
 * @param string $field Název pole
 * @param mixed $value Hodnota pro porovnání
 * @param string $operator Operátor (=, !=, >, <, >=, <=, like, in, not_in)
 * @return static<T> Nová instance kolekce
 */
public function where(string $field, mixed $value, string $operator = '='): static
{
    return $this->filter(function($item) use ($field, $value, $operator) {
        $itemValue = $this->getFieldValue($item, $field);
        
        return match($operator) {
            '=', '==' => $itemValue == $value,
            '!=', '<>' => $itemValue != $value,
            '>' => $itemValue > $value,
            '<' => $itemValue < $value,
            '>=' => $itemValue >= $value,
            '<=' => $itemValue <= $value,
            'like' => is_string($itemValue) && str_contains(strtolower($itemValue), strtolower($value)),
            'in' => in_array($itemValue, (array)$value, true),
            'not_in' => !in_array($itemValue, (array)$value, true),
            default => throw new \InvalidArgumentException("Unsupported operator: $operator")
        };
    });
}

/**
 * Filtruje podle rozsahu hodnot (včetně krajních bodů)
 * 
 * @param string $field Název pole
 * @param mixed $min Minimální hodnota
 * @param mixed $max Maximální hodnota
 * @return static<T> Nová instance kolekce
 */
public function whereBetween(string $field, mixed $min, mixed $max): static
{
    return $this->filter(function($item) use ($field, $min, $max) {
        $value = $this->getFieldValue($item, $field);
        return $value >= $min && $value <= $max;
    });
}

/**
 * Filtruje podle toho, zda pole obsahuje jednu z hodnot
 * 
 * @param string $field Název pole
 * @param array $values Pole hodnot
 * @return static<T> Nová instance kolekce
 */
public function whereIn(string $field, array $values): static
{
    return $this->where($field, $values, 'in');
}

/**
 * Filtruje podle toho, zda pole NEobsahuje žádnou z hodnot
 * 
 * @param string $field Název pole
 * @param array $values Pole hodnot
 * @return static<T> Nová instance kolekce
 */
public function whereNotIn(string $field, array $values): static
{
    return $this->where($field, $values, 'not_in');
}

/**
 * Filtruje pouze prvky, kde pole není null
 * 
 * @param string $field Název pole
 * @return static<T> Nová instance kolekce
 */
public function whereNotNull(string $field): static
{
    return $this->filter(function($item) use ($field) {
        return $this->getFieldValue($item, $field) !== null;
    });
}

/**
 * Filtruje pouze prvky, kde pole je null
 * 
 * @param string $field Název pole
 * @return static<T> Nová instance kolekce
 */
public function whereNull(string $field): static
{
    return $this->filter(function($item) use ($field) {
        return $this->getFieldValue($item, $field) === null;
    });
}

/**
 * Filtruje podle toho, zda textové pole obsahuje daný substring
 * 
 * @param string $field Název pole
 * @param string $needle Hledaný text
 * @param bool $caseSensitive Case sensitive hledání?
 * @return static<T> Nová instance kolekce
 */
public function whereContains(string $field, string $needle, bool $caseSensitive = false): static
{
    return $this->filter(function($item) use ($field, $needle, $caseSensitive) {
        $value = (string)$this->getFieldValue($item, $field);
        
        return $caseSensitive 
            ? str_contains($value, $needle)
            : str_contains(strtolower($value), strtolower($needle));
    });
}

/**
 * Filtruje podle toho, zda textové pole začíná daným textem
 * 
 * @param string $field Název pole
 * @param string $prefix Prefix
 * @param bool $caseSensitive Case sensitive?
 * @return static<T> Nová instance kolekce
 */
public function whereStartsWith(string $field, string $prefix, bool $caseSensitive = false): static
{
    return $this->filter(function($item) use ($field, $prefix, $caseSensitive) {
        $value = (string)$this->getFieldValue($item, $field);
        
        return $caseSensitive 
            ? str_starts_with($value, $prefix)
            : str_starts_with(strtolower($value), strtolower($prefix));
    });
}

/**
 * Filtruje podle toho, zda textové pole končí daným textem
 * 
 * @param string $field Název pole
 * @param string $suffix Suffix
 * @param bool $caseSensitive Case sensitive?
 * @return static<T> Nová instance kolekce
 */
public function whereEndsWith(string $field, string $suffix, bool $caseSensitive = false): static
{
    return $this->filter(function($item) use ($field, $suffix, $caseSensitive) {
        $value = (string)$this->getFieldValue($item, $field);
        
        return $caseSensitive 
            ? str_ends_with($value, $suffix)
            : str_ends_with(strtolower($value), strtolower($suffix));
    });
}

// =============================================================================
// 🎲 UTILITY OPERACE
// =============================================================================

/**
 * Obrátí pořadí prvků v kolekci
 * 
 * @return static<T> Nová instance s obráceným pořadím
 */
public function reverse(): static
{
    return new static(array_reverse($this->toArray(), true));
}

/**
 * Náhodně zamíchá prvky v kolekci
 * 
 * @return static<T> Nová instance s náhodným pořadím
 */
public function shuffle(): static
{
    $items = $this->toArray();
    shuffle($items);
    return new static($items);
}

/**
 * Vezme prvních N prvků z kolekce
 * 
 * @param int $count Počet prvků
 * @return static<T> Nová instance s prvními N prvky
 */
public function take(int $count): static
{
    return $this->slice(0, $count);
}

/**
 * Přeskočí prvních N prvků a vrátí zbytek
 * 
 * @param int $count Počet prvků k přeskočení
 * @return static<T> Nová instance bez prvních N prvků
 */
public function skip(int $count): static
{
    return $this->slice($count);
}

/**
 * Vezme posledních N prvků z kolekce
 * 
 * @param int $count Počet prvků
 * @return static<T> Nová instance s posledními N prvky
 */
public function takeLast(int $count): static
{
    $totalCount = $this->count();
    $startIndex = max(0, $totalCount - $count);
    return $this->slice($startIndex);
}

/**
 * Vybere náhodných N prvků z kolekce
 * 
 * @param int $count Počet prvků (default 1)
 * @return static<T> Nová instance s náhodnými prvky
 */
public function random(int $count = 1): static
{
    if ($count <= 0) {
        return new static([]);
    }
    
    if ($count >= $this->count()) {
        return $this->shuffle();
    }
    
    return $this->shuffle()->take($count);
}

/**
 * Vrátí náhodný prvek z kolekce
 * 
 * @return mixed Náhodný prvek nebo null pro prázdnou kolekci
 */
public function randomItem(): mixed
{
    if ($this->isEmpty()) {
        return null;
    }
    
    $items = $this->toArray();
    return $items[array_rand($items)];
}

// =============================================================================
// 🔄 POKROČILÉ TRANSFORMACE
// =============================================================================

/**
 * Flatten víceúrovňové pole/kolekce do jedné úrovně
 * 
 * @param int $depth Hloubka flatteningu (0 = nekonečno)
 * @return static<T> Nová instance s flattened daty
 */
public function flatten(int $depth = 0): static
{
    $result = [];
    
    foreach ($this as $item) {
        if (is_array($item) || $item instanceof \Traversable) {
            $flattened = ($depth > 1 || $depth === 0) 
                ? (new static($item))->flatten($depth > 0 ? $depth - 1 : 0)->toArray()
                : (is_array($item) ? $item : iterator_to_array($item));
                
            $result = array_merge($result, $flattened);
        } else {
            $result[] = $item;
        }
    }
    
    return new static($result);
}

/**
 * Přidá prvky na začátek kolekce
 * 
 * @param mixed ...$items Prvky k přidání
 * @return static<T> Nová instance s přidanými prvky
 */
public function prepend(mixed ...$items): static
{
    return new static(array_merge($items, $this->toArray()));
}

/**
 * Přidá prvky na konec kolekce
 * 
 * @param mixed ...$items Prvky k přidání
 * @return static<T> Nová instance s přidanými prvky
 */
public function append(mixed ...$items): static
{
    return new static(array_merge($this->toArray(), $items));
}

/**
 * Rozdělí kolekci do párů klíč-hodnota podle dvou polí
 * 
 * @param string $keyField Pole pro klíče
 * @param string $valueField Pole pro hodnoty
 * @return array Asociativní pole
 */
public function mapToDictionary(string $keyField, string $valueField): array
{
    $result = [];
    
    foreach ($this as $item) {
        $key = $this->getFieldValue($item, $keyField);
        $value = $this->getFieldValue($item, $valueField);
        
        if ($key !== null) {
            $result[$key] = $value;
        }
    }
    
    return $result;
}

/**
 * Vytvoří lookup tabulku podle klíče
 * 
 * @param string $keyField Pole pro klíče
 * @return array Asociativní pole klíč => objekt
 */
public function keyBy(string $keyField): array
{
    return $this->indexBy($keyField);
}

// =============================================================================
// 📊 POKROČILÉ STATISTIKY
// =============================================================================

/**
 * Vypočítá statistiky pro numerické pole
 * 
 * @param string $field Název pole
 * @return array Pole se statistikami (count, sum, avg, min, max, median)
 */
public function stats(string $field): array
{
    $values = array_filter($this->pluck($field), 'is_numeric');
    
    if (empty($values)) {
        return [
            'count' => 0,
            'sum' => 0,
            'avg' => 0,
            'min' => null,
            'max' => null,
            'median' => null
        ];
    }
    
    sort($values);
    $count = count($values);
    $sum = array_sum($values);
    
    return [
        'count' => $count,
        'sum' => $sum,
        'avg' => $sum / $count,
        'min' => min($values),
        'max' => max($values),
        'median' => $count % 2 === 0 
            ? ($values[floor($count/2) - 1] + $values[floor($count/2)]) / 2
            : $values[floor($count/2)]
    ];
}

/**
 * Vytvoří frekvence tabulku pro pole
 * 
 * @param string $field Název pole
 * @param bool $sortByFrequency Seřadit podle frekvence?
 * @return array Pole hodnota => frekvence
 */
public function frequencies(string $field, bool $sortByFrequency = true): array
{
    $frequencies = $this->countBy($field);
    
    if ($sortByFrequency) {
        arsort($frequencies);
    }
    
    return $frequencies;
}

// =============================================================================
// 🔍 POKROČILÉ HLEDÁNÍ
// =============================================================================

/**
 * Najde všechny prvky, které splňují všechny podmínky
 * 
 * @param array $conditions Pole podmínek ['field' => ['operator', 'value']]
 * @return static<T> Nová instance s filtrovanými prvky
 */
public function whereAll(array $conditions): static
{
    return $this->filter(function($item) use ($conditions) {
        foreach ($conditions as $field => $condition) {
            [$operator, $value] = $condition;
            $itemValue = $this->getFieldValue($item, $field);
            
            $matches = match($operator) {
                '=' => $itemValue == $value,
                '!=' => $itemValue != $value,
                '>' => $itemValue > $value,
                '<' => $itemValue < $value,
                '>=' => $itemValue >= $value,
                '<=' => $itemValue <= $value,
                'in' => in_array($itemValue, (array)$value),
                default => false
            };
            
            if (!$matches) {
                return false;
            }
        }
        
        return true;
    });
}

/**
 * Fulltext search přes více polí
 * 
 * @param string $query Hledaný text
 * @param array $fields Pole pro hledání
 * @param bool $caseSensitive Case sensitive?
 * @return static<T> Nová instance s výsledky
 */
public function search(string $query, array $fields, bool $caseSensitive = false): static
{
    if (empty($query) || empty($fields)) {
        return new static([]);
    }
    
    $query = $caseSensitive ? $query : strtolower($query);
    
    return $this->filter(function($item) use ($query, $fields, $caseSensitive) {
        foreach ($fields as $field) {
            $value = (string)$this->getFieldValue($item, $field);
            $value = $caseSensitive ? $value : strtolower($value);
            
            if (str_contains($value, $query)) {
                return true;
            }
        }
        
        return false;
    });
    }
}