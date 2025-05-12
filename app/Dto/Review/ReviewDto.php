<?php

declare(strict_types=1);

namespace App\Dto\Review;

use App\Dto\BaseDto;
use DateTime;

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
 * DTO for creating a new review
 */
class ReviewCreateDto extends BaseDto
{
    public int $addon_id;
    public ?int $user_id = null;
    public ?string $name = null;
    public ?string $email = null;
    public int $rating;
    public ?string $comment = null;
    
    /**
     * Validate the DTO
     * 
     * @return array Validation errors or empty array if valid
     */
    public function validate(): array
    {
        $errors = [];
        
        if (empty($this->addon_id)) {
            $errors['addon_id'] = 'Addon ID is required';
        }
        
        if ($this->rating < 1 || $this->rating > 5) {
            $errors['rating'] = 'Rating must be between 1 and 5';
        }
        
        // If user_id is not provided, name is required
        if (empty($this->user_id) && empty($this->name)) {
            $errors['name'] = 'Name is required for guest reviews';
        }
        
        // Email validation if provided
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        return $errors;
    }
}

/**
 * DTO for review statistics
 */
class ReviewStatsDto extends BaseDto
{
    public float $average_rating;
    public int $total_reviews;
    
    /**
     * Rating distribution (count of reviews for each rating value)
     * @var array<int, int>
     */
    public array $rating_distribution = [];
}

/**
 * DTO for sentiment analysis of reviews
 */
class ReviewSentimentDto extends BaseDto
{
    public int $positive;
    public int $neutral;
    public int $negative;
    public float $sentiment_score;
}

/**
 * DTO for review activity over time
 */
class ReviewActivityDto extends BaseDto
{
    /**
     * Review activity data
     * @var array<array{period: string, review_count: int, average_rating: float}>
     */
    public array $activity = [];
}

/**
 * DTO for common keywords in reviews
 */
class ReviewKeywordsDto extends BaseDto
{
    /**
     * Keywords and their frequencies
     * @var array<string, int>
     */
    public array $keywords = [];
}

namespace App\Dto\Tag;

use App\Dto\BaseDto;
use App\Dto\Addon\AddonDto;

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
 * DTO for creating a new tag
 */
class TagCreateDto extends BaseDto
{
    public string $name;
    public ?string $slug = null;
    
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
 * DTO for tag with addon count
 */
class TagWithCountDto extends BaseDto
{
    public TagDto $tag;
    public int $addon_count;
}

/**
 * DTO for related tag
 */
class RelatedTagDto extends BaseDto
{
    public TagDto $tag;
    public int $frequency;
}

/**
 * DTO for trending tag
 */
class TrendingTagDto extends BaseDto
{
    public TagDto $tag;
    public int $usage_count;
}

/**
 * DTO for tag cloud item
 */
class TagCloudItemDto extends BaseDto
{
    public int $id;
    public string $name;
    public string $slug;
    public int $weight;
    public int $normalized_weight;
}