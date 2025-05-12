<?php

declare(strict_types=1);

namespace App\Dto\Factory;

use App\Dto\BaseDto;
use App\Dto\PaginationDto;
use App\Dto\PaginatedListDto;
use App\Collection\PaginatedCollection;
use App\Dto\Addon\AddonDto;
use App\Dto\Addon\AddonDetailDto;
use App\Dto\Addon\AddonCreateDto;
use App\Dto\Addon\AddonUpdateDto;
use App\Dto\Addon\ScreenshotDto;
use App\Dto\Addon\ScreenshotCreateDto;
use App\Dto\Author\AuthorDto;
use App\Dto\Author\AuthorCreateDto;
use App\Dto\Author\AuthorUpdateDto;
use App\Dto\Author\AuthorDetailDto;
use App\Dto\Category\CategoryDto;
use App\Dto\Category\CategoryCreateDto;
use App\Dto\Category\CategoryUpdateDto;
use App\Dto\Category\CategoryDetailDto;
use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewCreateDto;
use App\Dto\Tag\TagDto;
use App\Dto\Tag\TagCreateDto;

/**
 * DTO Factory for creating DTOs from request data
 */
class DtoFactory
{
    /**
     * Create DTO from request data
     * 
     * @param string $dtoClass DTO class name
     * @param array $data Request data
     * @return BaseDto
     * @throws \InvalidArgumentException If DTO class is not supported
     */
    public function createFromRequest(string $dtoClass, array $data): BaseDto
    {
        if (!is_subclass_of($dtoClass, BaseDto::class)) {
            throw new \InvalidArgumentException("Class {$dtoClass} is not a valid DTO");
        }
        
        return $dtoClass::fromArray($data);
    }
    
    /**
     * Create Addon create DTO from request data
     * 
     * @param array $data
     * @return AddonCreateDto
     */
    public function createAddonCreateDto(array $data): AddonCreateDto
    {
        $dto = new AddonCreateDto();
        
        // Basic properties
        $dto->name = $data['name'] ?? '';
        $dto->slug = $data['slug'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->version = $data['version'] ?? '';
        $dto->author_id = (int)($data['author_id'] ?? 0);
        $dto->category_id = (int)($data['category_id'] ?? 0);
        $dto->repository_url = $data['repository_url'] ?? null;
        $dto->download_url = $data['download_url'] ?? '';
        $dto->icon_url = $data['icon_url'] ?? null;
        $dto->fanart_url = $data['fanart_url'] ?? null;
        $dto->kodi_version_min = $data['kodi_version_min'] ?? null;
        $dto->kodi_version_max = $data['kodi_version_max'] ?? null;
        
        // Tag IDs
        $dto->tag_ids = isset($data['tag_ids']) ? 
            (is_array($data['tag_ids']) ? array_map('intval', $data['tag_ids']) : []) 
            : [];
        
        // Screenshots
        $dto->screenshots = [];
        if (isset($data['screenshots']) && is_array($data['screenshots'])) {
            foreach ($data['screenshots'] as $screenshotData) {
                $screenshotDto = new ScreenshotCreateDto();
                $screenshotDto->url = $screenshotData['url'] ?? '';
                $screenshotDto->description = $screenshotData['description'] ?? null;
                $screenshotDto->sort_order = (int)($screenshotData['sort_order'] ?? 0);
                
                $dto->screenshots[] = $screenshotDto;
            }
        }
        
        return $dto;
    }
    
    /**
     * Create Addon update DTO from request data
     * 
     * @param array $data
     * @return AddonUpdateDto
     */
    public function createAddonUpdateDto(array $data): AddonUpdateDto
    {
        $dto = new AddonUpdateDto();
        
        // Basic properties
        $dto->id = (int)($data['id'] ?? 0);
        $dto->name = $data['name'] ?? '';
        $dto->slug = $data['slug'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->version = $data['version'] ?? '';
        $dto->author_id = (int)($data['author_id'] ?? 0);
        $dto->category_id = (int)($data['category_id'] ?? 0);
        $dto->repository_url = $data['repository_url'] ?? null;
        $dto->download_url = $data['download_url'] ?? '';
        $dto->icon_url = $data['icon_url'] ?? null;
        $dto->fanart_url = $data['fanart_url'] ?? null;
        $dto->kodi_version_min = $data['kodi_version_min'] ?? null;
        $dto->kodi_version_max = $data['kodi_version_max'] ?? null;
        
        // Tag IDs
        $dto->tag_ids = isset($data['tag_ids']) ? 
            (is_array($data['tag_ids']) ? array_map('intval', $data['tag_ids']) : []) 
            : [];
        
        // Screenshots
        $dto->screenshots = [];
        if (isset($data['screenshots']) && is_array($data['screenshots'])) {
            foreach ($data['screenshots'] as $screenshotData) {
                $screenshotDto = new ScreenshotCreateDto();
                $screenshotDto->url = $screenshotData['url'] ?? '';
                $screenshotDto->description = $screenshotData['description'] ?? null;
                $screenshotDto->sort_order = (int)($screenshotData['sort_order'] ?? 0);
                
                $dto->screenshots[] = $screenshotDto;
            }
        }
        
        return $dto;
    }
    
    /**
     * Create Author create DTO from request data
     * 
     * @param array $data
     * @return AuthorCreateDto
     */
    public function createAuthorCreateDto(array $data): AuthorCreateDto
    {
        $dto = new AuthorCreateDto();
        
        $dto->name = $data['name'] ?? '';
        $dto->email = $data['email'] ?? null;
        $dto->website = $data['website'] ?? null;
        
        return $dto;
    }
    
    /**
     * Create Author update DTO from request data
     * 
     * @param array $data
     * @return AuthorUpdateDto
     */
    public function createAuthorUpdateDto(array $data): AuthorUpdateDto
    {
        $dto = new AuthorUpdateDto();
        
        $dto->id = (int)($data['id'] ?? 0);
        $dto->name = $data['name'] ?? '';
        $dto->email = $data['email'] ?? null;
        $dto->website = $data['website'] ?? null;
        
        return $dto;
    }
    
    /**
     * Create Category create DTO from request data
     * 
     * @param array $data
     * @return CategoryCreateDto
     */
    public function createCategoryCreateDto(array $data): CategoryCreateDto
    {
        $dto = new CategoryCreateDto();
        
        $dto->name = $data['name'] ?? '';
        $dto->slug = $data['slug'] ?? null;
        $dto->parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
        
        return $dto;
    }
    
    /**
     * Create Category update DTO from request data
     * 
     * @param array $data
     * @return CategoryUpdateDto
     */
    public function createCategoryUpdateDto(array $data): CategoryUpdateDto
    {
        $dto = new CategoryUpdateDto();
        
        $dto->id = (int)($data['id'] ?? 0);
        $dto->name = $data['name'] ?? '';
        $dto->slug = $data['slug'] ?? null;
        $dto->parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
        
        return $dto;
    }
    
    /**
     * Create Review create DTO from request data
     * 
     * @param array $data
     * @return ReviewCreateDto
     */
    public function createReviewCreateDto(array $data): ReviewCreateDto
    {
        $dto = new ReviewCreateDto();
        
        $dto->addon_id = (int)($data['addon_id'] ?? 0);
        $dto->user_id = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $dto->name = $data['name'] ?? null;
        $dto->email = $data['email'] ?? null;
        $dto->rating = max(1, min(5, (int)($data['rating'] ?? 1)));
        $dto->comment = $data['comment'] ?? null;
        
        return $dto;
    }
    
    /**
     * Create Tag create DTO from request data
     * 
     * @param array $data
     * @return TagCreateDto
     */
    public function createTagCreateDto(array $data): TagCreateDto
    {
        $dto = new TagCreateDto();
        
        $dto->name = $data['name'] ?? '';
        $dto->slug = $data['slug'] ?? null;
        
        return $dto;
    }
    
    /**
     * Create paginated list DTO from paginated collection
     * 
     * @param PaginatedCollection $collection
     * @param callable $itemMapper Function to map each item to a DTO
     * @return PaginatedListDto
     */
    public function createPaginatedListDto(PaginatedCollection $collection, callable $itemMapper): PaginatedListDto
    {
        $paginationDto = PaginationDto::fromPaginatedCollection($collection);
        
        $items = [];
        foreach ($collection->getItems() as $item) {
            $items[] = $itemMapper($item);
        }
        
        return PaginatedListDto::create($items, $paginationDto);
    }
}