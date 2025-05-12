<?php

declare(strict_types=1);

namespace App\Dto\Category;

use App\Dto\BaseDto;
use App\Dto\Addon\AddonDto;

/**
 * DTO for category information
 */
class CategoryDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
    public ?int $parent_id;
}

/**
 * DTO for creating a new category
 */
class CategoryCreateDto extends BaseDto
{
    public string $name;
    public ?string $slug = null;
    public ?int $parent_id = null;
    
    /**
     * Validate the DTO
     * 
     * @return array Validation errors or empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->name)) {
            $errors['name'] = 'Name is required';
        }
        
        return $errors;
    }
}

/**
 * DTO for updating an existing category
 */
class CategoryUpdateDto extends BaseDto
{
    public int $id;
    public string $name;
    public ?string $slug = null;
    public ?int $parent_id = null;
    
    /**
     * Validate the DTO
     * 
     * @return array Validation errors or empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->id)) {
            $errors['id'] = 'ID is required';
        }
        
        if (empty($this->name)) {
            $errors['name'] = 'Name is required';
        }
        
        return $errors;
    }
}

/**
 * DTO for detailed category information with addons
 */
class CategoryDetailDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
    public ?int $parent_id;
    
    /**
     * Array of addon DTOs
     * @var array<AddonDto>
     */
    public array $addons = [];
    
    /**
     * Array of subcategory DTOs
     * @var array<CategoryDto>
     */
    public array $subcategories = [];
}

/**
 * DTO for category with statistics
 */
class CategoryWithStatsDto extends BaseDto
{
    public CategoryDto $category;
    public int $addon_count;
    public int $total_addon_count; // Including addons from subcategories
    
    /**
     * Array of subcategory DTOs with stats
     * @var array<CategoryWithStatsDto>
     */
    public array $subcategories = [];
}

/**
 * DTO for category path (breadcrumbs)
 */
class CategoryPathDto extends BaseDto
{
    /**
     * Array of category DTOs representing the path
     * @var array<CategoryDto>
     */
    public array $path = [];
    
    /**
     * Get the current category (last in path)
     * 
     * @return CategoryDto|null
     */
    public function getCurrentCategory(): ?CategoryDto
    {
        if (empty($this->path)) {
            return null;
        }
        
        return $this->path[count($this->path) - 1];
    }
}

/**
 * DTO for popular category with download stats
 */
class PopularCategoryDto extends BaseDto
{
    public CategoryDto $category;
    public int $addon_count;
    public int $total_downloads;
}