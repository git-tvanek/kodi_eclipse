<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette\SmartObject;

class Addon
{
    use SmartObject;

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
    public int $downloads_count = 0;
    public float $rating = 0.00;
    public DateTime $created_at;
    public DateTime $updated_at;

    /**
     * Create an Addon instance from array data
     */
    public static function fromArray(array $data): self
    {
        $addon = new self();
        
        if (isset($data['id'])) {
            $addon->id = (int) $data['id'];
        }
        
        $addon->name = $data['name'];
        $addon->slug = $data['slug'];
        $addon->description = $data['description'] ?? null;
        $addon->version = $data['version'];
        $addon->author_id = (int) $data['author_id'];
        $addon->category_id = (int) $data['category_id'];
        $addon->repository_url = $data['repository_url'] ?? null;
        $addon->download_url = $data['download_url'];
        $addon->icon_url = $data['icon_url'] ?? null;
        $addon->fanart_url = $data['fanart_url'] ?? null;
        $addon->kodi_version_min = $data['kodi_version_min'] ?? null;
        $addon->kodi_version_max = $data['kodi_version_max'] ?? null;
        $addon->downloads_count = (int) ($data['downloads_count'] ?? 0);
        $addon->rating = (float) ($data['rating'] ?? 0.00);
        
        if (isset($data['created_at'])) {
            $addon->created_at = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
        } else {
            $addon->created_at = new DateTime();
        }
        
        if (isset($data['updated_at'])) {
            $addon->updated_at = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
        } else {
            $addon->updated_at = new DateTime();
        }
        
        return $addon;
    }

    /**
     * Convert the Addon instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'version' => $this->version,
            'author_id' => $this->author_id,
            'category_id' => $this->category_id,
            'repository_url' => $this->repository_url,
            'download_url' => $this->download_url,
            'icon_url' => $this->icon_url,
            'fanart_url' => $this->fanart_url,
            'kodi_version_min' => $this->kodi_version_min,
            'kodi_version_max' => $this->kodi_version_max,
            'downloads_count' => $this->downloads_count,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}