<?php

declare(strict_types=1);

namespace App\Dto\Addon;

use App\Dto\BaseDto;
use DateTime;

/**
 * DTO for basic addon information
 */
class AddonDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $version;
    public int $author_id;
    public int $category_id;
    public ?string $repository_url;
    public string $download_url;
    public ?string $icon_url;
    public ?string $fanart_url;
    public ?string $kodi_version_min;
    public ?string $kodi_version_max;
    public int $downloads_count;
    public float $rating;
    public string $created_at;
    public string $updated_at;
    
    /**
     * Array of tag IDs
     * @var array<int>
     */
    public array $tag_ids = [];
}

/**
 * DTO for creating a new addon
 */
class AddonCreateDto extends BaseDto
{
    public string $name;
    public ?string $slug = null;
    public ?string $description = null;
    public string $version;
    public int $author_id;
    public int $category_id;
    public ?string $repository_url = null;
    public string $download_url;
    public ?string $icon_url = null;
    public ?string $fanart_url = null;
    public ?string $kodi_version_min = null;
    public ?string $kodi_version_max = null;
    
    /**
     * Array of tag IDs
     * @var array<int>
     */
    public array $tag_ids = [];
    
    /**
     * Array of screenshot DTOs
     * @var array<ScreenshotCreateDto>
     */
    public array $screenshots = [];
    
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
        
        if (empty($this->version)) {
            $errors['version'] = 'Version is required';
        }
        
        if (empty($this->author_id)) {
            $errors['author_id'] = 'Author ID is required';
        }
        
        if (empty($this->category_id)) {
            $errors['category_id'] = 'Category ID is required';
        }
        
        if (empty($this->download_url)) {
            $errors['download_url'] = 'Download URL is required';
        }
        
        return $errors;
    }
}

/**
 * DTO for updating an existing addon
 */
class AddonUpdateDto extends BaseDto
{
    public int $id;
    public string $name;
    public ?string $slug = null;
    public ?string $description = null;
    public string $version;
    public int $author_id;
    public int $category_id;
    public ?string $repository_url = null;
    public string $download_url;
    public ?string $icon_url = null;
    public ?string $fanart_url = null;
    public ?string $kodi_version_min = null;
    public ?string $kodi_version_max = null;
    
    /**
     * Array of tag IDs
     * @var array<int>
     */
    public array $tag_ids = [];
    
    /**
     * Array of screenshot DTOs
     * @var array<ScreenshotCreateDto>
     */
    public array $screenshots = [];
    
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
        
        if (empty($this->version)) {
            $errors['version'] = 'Version is required';
        }
        
        if (empty($this->author_id)) {
            $errors['author_id'] = 'Author ID is required';
        }
        
        if (empty($this->category_id)) {
            $errors['category_id'] = 'Category ID is required';
        }
        
        if (empty($this->download_url)) {
            $errors['download_url'] = 'Download URL is required';
        }
        
        return $errors;
    }
}

/**
 * DTO for detailed addon information with related data
 */
class AddonDetailDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
    public ?string $description;
    public string $version;
    public int $author_id;
    public string $author_name;
    public int $category_id;
    public string $category_name;
    public ?string $repository_url;
    public string $download_url;
    public ?string $icon_url;
    public ?string $fanart_url;
    public ?string $kodi_version_min;
    public ?string $kodi_version_max;
    public int $downloads_count;
    public float $rating;
    public string $created_at;
    public string $updated_at;
    
    /**
     * Array of screenshot DTOs
     * @var array<ScreenshotDto>
     */
    public array $screenshots = [];
    
    /**
     * Array of tag DTOs
     * @var array<TagDto>
     */
    public array $tags = [];
    
    /**
     * Array of review DTOs
     * @var array<ReviewDto>
     */
    public array $reviews = [];
}

/**
 * DTO for screenshot information
 */
class ScreenshotDto extends BaseDto
{
    public int $id;
    public int $addon_id;
    public string $url;
    public ?string $description;
    public int $sort_order;
}

/**
 * DTO for creating a new screenshot
 */
class ScreenshotCreateDto extends BaseDto
{
    public ?int $addon_id = null;
    public string $url;
    public ?string $description = null;
    public int $sort_order = 0;
}

/**
 * DTO for tag information
 */
class TagDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
}

/**
 * DTO for review information
 */
class ReviewDto extends BaseDto
{
    public int $id;
    public int $addon_id;
    public ?int $user_id;
    public ?string $name;
    public ?string $email;
    public int $rating;
    public ?string $comment;
    public string $created_at;
}

/**
 * DTO for search filters
 */
class AddonSearchFiltersDto extends BaseDto
{
    public ?string $query = null;
    public ?array $category_ids = null;
    public ?array $author_ids = null;
    public ?array $tag_ids = null;
    public ?float $min_rating = null;
    public ?float $max_rating = null;
    public ?int $min_downloads = null;
    public ?int $max_downloads = null;
    public ?string $kodi_version = null;
    public ?string $sort_by = 'name';
    public ?string $sort_dir = 'ASC';
}

/**
 * DTO for search results
 */
class AddonSearchResultDto extends BaseDto
{
    public string $query;
    
    /**
     * Array of addon DTOs
     * @var array<AddonDto>
     */
    public array $addons = [];
    
    /**
     * Array of tag DTOs
     * @var array<TagDto>
     */
    public array $related_tags = [];
    
    /**
     * Pagination information
     */
    public \App\Dto\PaginationDto $pagination;
}