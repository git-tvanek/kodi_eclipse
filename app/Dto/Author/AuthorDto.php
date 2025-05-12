<?php

declare(strict_types=1);

namespace App\Dto\Author;

use App\Dto\BaseDto;
use App\Dto\Addon\AddonDto;
use DateTime;

/**
 * DTO for author information
 */
class AuthorDto extends BaseDto
{
    public int $id;
    public string $name;
    public ?string $email;
    public ?string $website;
    public string $created_at;
}

/**
 * DTO for creating a new author
 */
class AuthorCreateDto extends BaseDto
{
    public string $name;
    public ?string $email = null;
    public ?string $website = null;
    
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
        
        // Email validation if provided
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Website URL validation if provided
        if (!empty($this->website) && !filter_var($this->website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Invalid website URL format';
        }
        
        return $errors;
    }
}

/**
 * DTO for updating an existing author
 */
class AuthorUpdateDto extends BaseDto
{
    public int $id;
    public string $name;
    public ?string $email = null;
    public ?string $website = null;
    
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
        
        // Email validation if provided
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        // Website URL validation if provided
        if (!empty($this->website) && !filter_var($this->website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Invalid website URL format';
        }
        
        return $errors;
    }
}

/**
 * DTO for detailed author information with addons
 */
class AuthorDetailDto extends BaseDto
{
    public int $id;
    public string $name;
    public ?string $email;
    public ?string $website;
    public string $created_at;
    
    /**
     * Array of addon DTOs
     * @var array<AddonDto>
     */
    public array $addons = [];
}

/**
 * DTO for author statistics
 */
class AuthorStatisticsDto extends BaseDto
{
    public AuthorDto $author;
    public int $addon_count;
    public int $total_downloads;
    public float $average_rating;
    
    /**
     * Category distribution
     * @var array<array{category_id: int, category_name: string, addon_count: int}>
     */
    public array $category_distribution = [];
    
    /**
     * Activity timeline
     * @var array<array{month: string, addon_count: int}>
     */
    public array $activity_timeline = [];
}

/**
 * DTO for author collaboration network
 */
class AuthorCollaborationNetworkDto extends BaseDto
{
    /**
     * Network nodes
     * @var array<array{id: int, name: string, level: int}>
     */
    public array $nodes = [];
    
    /**
     * Network links
     * @var array<array{source: int, target: int, strength: int}>
     */
    public array $links = [];
}

/**
 * DTO for author filter options
 */
class AuthorFilterDto extends BaseDto
{
    public ?string $name = null;
    public ?string $email = null;
    public ?int $min_addons = null;
    public ?bool $has_website = null;
    public ?DateTime $created_after = null;
    public ?DateTime $created_before = null;
}