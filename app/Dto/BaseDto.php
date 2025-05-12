<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Base abstract DTO class that all other DTOs will extend
 */
abstract class BaseDto
{
    /**
     * Convert DTO to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        
        // Get public properties using reflection
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $this->$name;
            
            // Handle nested DTOs
            if ($value instanceof BaseDto) {
                $result[$name] = $value->toArray();
            } elseif (is_array($value)) {
                // Handle arrays of DTOs
                $result[$name] = $this->processArrayValue($value);
            } else {
                $result[$name] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Process array values, converting nested DTOs to arrays
     * 
     * @param array $array
     * @return array
     */
    private function processArrayValue(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if ($value instanceof BaseDto) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = $this->processArrayValue($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Create DTO from array data
     * 
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }
        
        return $dto;
    }
}

/**
 * Base interface for all DTO mappers
 * 
 * @template TModel
 * @template TDto
 */
interface IDtoMapper
{
    /**
     * Map from entity to DTO
     * 
     * @param TModel $entity
     * @return TDto
     */
    public function toDto(object $entity): object;
    
    /**
     * Map from DTO to entity
     * 
     * @param TDto $dto
     * @param TModel|null $entity
     * @return TModel
     */
    public function toEntity(object $dto, ?object $entity = null): object;
    
    /**
     * Map collection of entities to DTOs
     * 
     * @param iterable<TModel> $entities
     * @return array<TDto>
     */
    public function toDtoCollection(iterable $entities): array;
}

/**
 * Base DTO mapper implementation
 * 
 * @template TModel
 * @template TDto
 * @implements IDtoMapper<TModel, TDto>
 */
abstract class BaseDtoMapper implements IDtoMapper
{
    /**
     * Map collection of entities to DTOs
     * 
     * @param iterable<TModel> $entities
     * @return array<TDto>
     */
    public function toDtoCollection(iterable $entities): array
    {
        $dtos = [];
        
        foreach ($entities as $entity) {
            $dtos[] = $this->toDto($entity);
        }
        
        return $dtos;
    }

    /**
     * Get entity class name
     * 
     * @return string
     */
    abstract protected function getEntityClass(): string;

    /**
     * Get DTO class name
     * 
     * @return string
     */
    abstract protected function getDtoClass(): string;
}

/**
 * Base pagination DTO for handling paginated collections
 */
class PaginationDto extends BaseDto
{
    public int $page;
    public int $itemsPerPage;
    public int $totalCount;
    public int $pages;
    public bool $hasNextPage;
    public bool $hasPreviousPage;
    public ?int $nextPage = null;
    public ?int $previousPage = null;
    
    /**
     * Create pagination DTO from paginated collection
     * 
     * @param App\Collection\PaginatedCollection $collection
     * @return self
     */
    public static function fromPaginatedCollection(\App\Collection\PaginatedCollection $collection): self
    {
        $dto = new self();
        $dto->page = $collection->getPage();
        $dto->itemsPerPage = $collection->getItemsPerPage();
        $dto->totalCount = $collection->getTotalCount();
        $dto->pages = $collection->getPages();
        $dto->hasNextPage = $collection->hasNextPage();
        $dto->hasPreviousPage = $collection->hasPreviousPage();
        $dto->nextPage = $collection->getNextPage();
        $dto->previousPage = $collection->getPreviousPage();
        
        return $dto;
    }
}

/**
 * Base paginated list DTO combining items with pagination info
 * 
 * @template T
 */
class PaginatedListDto extends BaseDto
{
    /** @var array<T> */
    public array $items = [];
    
    public PaginationDto $pagination;
    
    /**
     * Create paginated list DTO from items and pagination info
     * 
     * @param array<T> $items
     * @param PaginationDto $pagination
     * @return self<T>
     */
    public static function create(array $items, PaginationDto $pagination): self
    {
        $dto = new self();
        $dto->items = $items;
        $dto->pagination = $pagination;
        
        return $dto;
    }
}

/**
 * Error DTO for handling error responses
 */
class ErrorDto extends BaseDto
{
    public string $message;
    public int $code;
    public ?array $details = null;
    
    /**
     * Create error DTO
     * 
     * @param string $message
     * @param int $code
     * @param array|null $details
     * @return self
     */
    public static function create(string $message, int $code = 400, ?array $details = null): self
    {
        $dto = new self();
        $dto->message = $message;
        $dto->code = $code;
        $dto->details = $details;
        
        return $dto;
    }
}