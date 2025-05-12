<?php

declare(strict_types=1);

namespace App\Dto\Mapper;

use App\Dto\BaseDtoMapper;
use App\Model\Addon;
use App\Model\Author;
use App\Model\Category;
use App\Model\AddonReview;
use App\Model\Tag;
use App\Model\Screenshot;
use App\Dto\Addon\AddonDto;
use App\Dto\Addon\AddonDetailDto;
use App\Dto\Addon\AddonCreateDto;
use App\Dto\Addon\AddonUpdateDto;
use App\Dto\Addon\ScreenshotDto;
use App\Dto\Addon\ScreenshotCreateDto;
use App\Dto\Addon\TagDto as AddonTagDto;
use App\Dto\Addon\ReviewDto as AddonReviewDto;
use App\Dto\Author\AuthorDto;
use App\Dto\Category\CategoryDto;
use App\Dto\Review\ReviewDto;
use App\Dto\Tag\TagDto;
use Nette\Utils\DateTime as NetteDateTime;

/**
 * DTO Mapper for Addon entities
 * 
 * @extends BaseDtoMapper<Addon, AddonDto>
 */
class AddonDtoMapper extends BaseDtoMapper
{
    /** @var ScreenshotDtoMapper */
    private ScreenshotDtoMapper $screenshotMapper;
    
    /** @var TagDtoMapper */
    private TagDtoMapper $tagMapper;
    
    /** @var ReviewDtoMapper */
    private ReviewDtoMapper $reviewMapper;
    
    /** @var AuthorDtoMapper */
    private AuthorDtoMapper $authorMapper;
    
    /** @var CategoryDtoMapper */
    private CategoryDtoMapper $categoryMapper;
    
    /**
     * Constructor
     * 
     * @param ScreenshotDtoMapper $screenshotMapper
     * @param TagDtoMapper $tagMapper
     * @param ReviewDtoMapper $reviewMapper
     * @param AuthorDtoMapper $authorMapper
     * @param CategoryDtoMapper $categoryMapper
     */
    public function __construct(
        ScreenshotDtoMapper $screenshotMapper,
        TagDtoMapper $tagMapper,
        ReviewDtoMapper $reviewMapper,
        AuthorDtoMapper $authorMapper,
        CategoryDtoMapper $categoryMapper
    ) {
        $this->screenshotMapper = $screenshotMapper;
        $this->tagMapper = $tagMapper;
        $this->reviewMapper = $reviewMapper;
        $this->authorMapper = $authorMapper;
        $this->categoryMapper = $categoryMapper;
    }
    
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Addon::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return AddonDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param Addon $entity
     * @return AddonDto
     */
    public function toDto(object $entity): object
    {
        $dto = new AddonDto();
        $dto->id = $entity->id;
        $dto->name = $entity->name;
        $dto->slug = $entity->slug;
        $dto->description = $entity->description;
        $dto->version = $entity->version;
        $dto->author_id = $entity->author_id;
        $dto->category_id = $entity->category_id;
        $dto->repository_url = $entity->repository_url;
        $dto->download_url = $entity->download_url;
        $dto->icon_url = $entity->icon_url;
        $dto->fanart_url = $entity->fanart_url;
        $dto->kodi_version_min = $entity->kodi_version_min;
        $dto->kodi_version_max = $entity->kodi_version_max;
        $dto->downloads_count = $entity->downloads_count;
        $dto->rating = $entity->rating;
        $dto->created_at = $entity->created_at->format('Y-m-d H:i:s');
        $dto->updated_at = $entity->updated_at->format('Y-m-d H:i:s');
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param AddonDto $dto
     * @param Addon|null $entity
     * @return Addon
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new Addon();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->name = $dto->name;
        $entity->slug = $dto->slug;
        $entity->description = $dto->description;
        $entity->version = $dto->version;
        $entity->author_id = $dto->author_id;
        $entity->category_id = $dto->category_id;
        $entity->repository_url = $dto->repository_url;
        $entity->download_url = $dto->download_url;
        $entity->icon_url = $dto->icon_url;
        $entity->fanart_url = $dto->fanart_url;
        $entity->kodi_version_min = $dto->kodi_version_min;
        $entity->kodi_version_max = $dto->kodi_version_max;
        $entity->downloads_count = $dto->downloads_count ?? 0;
        $entity->rating = $dto->rating ?? 0.0;
        
        // Only set timestamps for new entities
        if (!isset($entity->id)) {
            $entity->created_at = new \DateTime();
            $entity->updated_at = new \DateTime();
        } else {
            $entity->updated_at = new \DateTime();
        }
        
        return $entity;
    }
    
    /**
     * Map from create DTO to entity
     * 
     * @param AddonCreateDto $dto
     * @return Addon
     */
    public function createDtoToEntity(AddonCreateDto $dto): Addon
    {
        $entity = new Addon();
        
        $entity->name = $dto->name;
        $entity->slug = $dto->slug ?? \Nette\Utils\Strings::webalize($dto->name);
        $entity->description = $dto->description;
        $entity->version = $dto->version;
        $entity->author_id = $dto->author_id;
        $entity->category_id = $dto->category_id;
        $entity->repository_url = $dto->repository_url;
        $entity->download_url = $dto->download_url;
        $entity->icon_url = $dto->icon_url;
        $entity->fanart_url = $dto->fanart_url;
        $entity->kodi_version_min = $dto->kodi_version_min;
        $entity->kodi_version_max = $dto->kodi_version_max;
        $entity->downloads_count = 0;
        $entity->rating = 0.0;
        $entity->created_at = new \DateTime();
        $entity->updated_at = new \DateTime();
        
        return $entity;
    }
    
    /**
     * Map from update DTO to entity
     * 
     * @param AddonUpdateDto $dto
     * @param Addon $entity
     * @return Addon
     */
    public function updateDtoToEntity(AddonUpdateDto $dto, Addon $entity): Addon
    {
        $entity->name = $dto->name;
        $entity->slug = $dto->slug ?? \Nette\Utils\Strings::webalize($dto->name);
        $entity->description = $dto->description;
        $entity->version = $dto->version;
        $entity->author_id = $dto->author_id;
        $entity->category_id = $dto->category_id;
        $entity->repository_url = $dto->repository_url;
        $entity->download_url = $dto->download_url;
        $entity->icon_url = $dto->icon_url;
        $entity->fanart_url = $dto->fanart_url;
        $entity->kodi_version_min = $dto->kodi_version_min;
        $entity->kodi_version_max = $dto->kodi_version_max;
        $entity->updated_at = new \DateTime();
        
        return $entity;
    }
    
    /**
     * Map from entity to detail DTO
     * 
     * @param Addon $entity
     * @param Author $author
     * @param Category $category
     * @param array<Screenshot> $screenshots
     * @param array<Tag> $tags
     * @param array<AddonReview> $reviews
     * @return AddonDetailDto
     */
    public function toDetailDto(
        Addon $entity,
        Author $author,
        Category $category,
        array $screenshots = [],
        array $tags = [],
        array $reviews = []
    ): AddonDetailDto {
        $dto = new AddonDetailDto();
        $dto->id = $entity->id;
        $dto->name = $entity->name;
        $dto->slug = $entity->slug;
        $dto->description = $entity->description;
        $dto->version = $entity->version;
        $dto->author_id = $entity->author_id;
        $dto->author_name = $author->name;
        $dto->category_id = $entity->category_id;
        $dto->category_name = $category->name;
        $dto->repository_url = $entity->repository_url;
        $dto->download_url = $entity->download_url;
        $dto->icon_url = $entity->icon_url;
        $dto->fanart_url = $entity->fanart_url;
        $dto->kodi_version_min = $entity->kodi_version_min;
        $dto->kodi_version_max = $entity->kodi_version_max;
        $dto->downloads_count = $entity->downloads_count;
        $dto->rating = $entity->rating;
        $dto->created_at = $entity->created_at->format('Y-m-d H:i:s');
        $dto->updated_at = $entity->updated_at->format('Y-m-d H:i:s');
        
        // Map screenshots
        $dto->screenshots = $this->screenshotMapper->toDtoCollection($screenshots);
        
        // Map tags
        $dto->tags = $this->tagMapper->toDtoCollection($tags);
        
        // Map reviews
        $dto->reviews = $this->reviewMapper->toDtoCollection($reviews);
        
        return $dto;
    }
}

/**
 * DTO Mapper for Author entities
 * 
 * @extends BaseDtoMapper<Author, AuthorDto>
 */
class AuthorDtoMapper extends BaseDtoMapper
{
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Author::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return AuthorDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param Author $entity
     * @return AuthorDto
     */
    public function toDto(object $entity): object
    {
        $dto = new AuthorDto();
        $dto->id = $entity->id;
        $dto->name = $entity->name;
        $dto->email = $entity->email;
        $dto->website = $entity->website;
        $dto->created_at = $entity->created_at->format('Y-m-d H:i:s');
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param AuthorDto $dto
     * @param Author|null $entity
     * @return Author
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new Author();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->name = $dto->name;
        $entity->email = $dto->email;
        $entity->website = $dto->website;
        
        // Only set created_at for new entities
        if (!isset($entity->id)) {
            $entity->created_at = new \DateTime();
        }
        
        return $entity;
    }
}

/**
 * DTO Mapper for Category entities
 * 
 * @extends BaseDtoMapper<Category, CategoryDto>
 */
class CategoryDtoMapper extends BaseDtoMapper
{
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Category::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return CategoryDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param Category $entity
     * @return CategoryDto
     */
    public function toDto(object $entity): object
    {
        $dto = new CategoryDto();
        $dto->id = $entity->id;
        $dto->name = $entity->name;
        $dto->slug = $entity->slug;
        $dto->parent_id = $entity->parent_id;
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param CategoryDto $dto
     * @param Category|null $entity
     * @return Category
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new Category();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->name = $dto->name;
        $entity->slug = $dto->slug;
        $entity->parent_id = $dto->parent_id;
        
        return $entity;
    }
}

/**
 * DTO Mapper for Review entities
 * 
 * @extends BaseDtoMapper<AddonReview, ReviewDto>
 */
class ReviewDtoMapper extends BaseDtoMapper
{
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return AddonReview::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return ReviewDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param AddonReview $entity
     * @return ReviewDto
     */
    public function toDto(object $entity): object
    {
        $dto = new ReviewDto();
        $dto->id = $entity->id;
        $dto->addon_id = $entity->addon_id;
        $dto->user_id = $entity->user_id;
        $dto->name = $entity->name;
        $dto->email = $entity->email;
        $dto->rating = $entity->rating;
        $dto->comment = $entity->comment;
        $dto->created_at = $entity->created_at->format('Y-m-d H:i:s');
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param ReviewDto $dto
     * @param AddonReview|null $entity
     * @return AddonReview
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new AddonReview();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->addon_id = $dto->addon_id;
        $entity->user_id = $dto->user_id;
        $entity->name = $dto->name;
        $entity->email = $dto->email;
        $entity->rating = $dto->rating;
        $entity->comment = $dto->comment;
        
        // Only set created_at for new entities
        if (!isset($entity->id)) {
            $entity->created_at = new \DateTime();
        }
        
        return $entity;
    }
}

/**
 * DTO Mapper for Tag entities
 * 
 * @extends BaseDtoMapper<Tag, TagDto>
 */
class TagDtoMapper extends BaseDtoMapper
{
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Tag::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return TagDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param Tag $entity
     * @return TagDto
     */
    public function toDto(object $entity): object
    {
        $dto = new TagDto();
        $dto->id = $entity->id;
        $dto->name = $entity->name;
        $dto->slug = $entity->slug;
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param TagDto $dto
     * @param Tag|null $entity
     * @return Tag
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new Tag();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->name = $dto->name;
        $entity->slug = $dto->slug;
        
        return $entity;
    }
}

/**
 * DTO Mapper for Screenshot entities
 * 
 * @extends BaseDtoMapper<Screenshot, ScreenshotDto>
 */
class ScreenshotDtoMapper extends BaseDtoMapper
{
    /**
     * Get entity class name
     * 
     * @return string
     */
    protected function getEntityClass(): string
    {
        return Screenshot::class;
    }
    
    /**
     * Get DTO class name
     * 
     * @return string
     */
    protected function getDtoClass(): string
    {
        return ScreenshotDto::class;
    }
    
    /**
     * Map from entity to DTO
     * 
     * @param Screenshot $entity
     * @return ScreenshotDto
     */
    public function toDto(object $entity): object
    {
        $dto = new ScreenshotDto();
        $dto->id = $entity->id;
        $dto->addon_id = $entity->addon_id;
        $dto->url = $entity->url;
        $dto->description = $entity->description;
        $dto->sort_order = $entity->sort_order;
        
        return $dto;
    }
    
    /**
     * Map from DTO to entity
     * 
     * @param ScreenshotDto $dto
     * @param Screenshot|null $entity
     * @return Screenshot
     */
    public function toEntity(object $dto, ?object $entity = null): object
    {
        if ($entity === null) {
            $entity = new Screenshot();
        }
        
        if (isset($dto->id)) {
            $entity->id = $dto->id;
        }
        
        $entity->addon_id = $dto->addon_id;
        $entity->url = $dto->url;
        $entity->description = $dto->description;
        $entity->sort_order = $dto->sort_order;
        
        return $entity;
    }
}