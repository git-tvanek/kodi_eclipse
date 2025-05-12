<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Tag;
use App\Model\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\ITagRepository;
use Nette\Database\Explorer;
use Nette\Utils\Strings;

/**
 * @extends BaseRepository<Tag>
 * @implements TagRepositoryInterface
 */
class TagRepository extends BaseRepository implements ITagRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'tags';
        $this->entityClass = Tag::class;
    }

    /**
     * Find tag by slug
     * 
     * @param string $slug
     * @return Tag|null
     */
    public function findBySlug(string $slug): ?Tag
    {
        /** @var Tag|null */
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Find or create a tag
     * 
     * @param string $name
     * @return int
     */
    public function findOrCreate(string $name): int
    {
        $slug = Strings::webalize($name);
        $tag = $this->findBySlug($slug);
        
        if ($tag) {
            return $tag->id;
        }
        
        $newTag = new Tag();
        $newTag->name = $name;
        $newTag->slug = $slug;
        
        return $this->save($newTag);
    }

    /**
     * Create a new tag
     * 
     * @param Tag $tag
     * @return int
     */
    public function create(Tag $tag): int
    {
        // Generate slug if not provided
        if (empty($tag->slug)) {
            $tag->slug = Strings::webalize($tag->name);
        }
        
        return $this->save($tag);
    }

    /**
    * Aktualizuje existující tag
    * 
    * @param Tag $tag Tag k aktualizaci
    * @return int ID aktualizovaného tagu
    */
    public function update(Tag $tag): int
    {
    // Kontrola, že tag existuje
    if (!$this->exists($tag->id)) {
        throw new \Exception("Tag s ID {$tag->id} neexistuje.");
    }
    
    // Pokud není slug definován, vytvoříme ho z názvu
    if (empty($tag->slug)) {
        $tag->slug = Strings::webalize($tag->name);
    }
    
    // Uložíme aktualizovaný tag do databáze
    $this->getTable()
        ->wherePrimary($tag->id)
        ->update($tag->toArray());
    
    return $tag->id;
    }

    /**
     * Get tags with their addon counts
     * 
     * @return array
     */
    public function getTagsWithCounts(): array
    {
        $result = $this->database->query('
            SELECT t.*, COUNT(at.addon_id) AS addon_count
            FROM tags t
            LEFT JOIN addon_tags at ON t.id = at.tag_id
            GROUP BY t.id
            ORDER BY t.name ASC
        ');
             
        $tags = [];
        foreach ($result as $row) {
            $tag = Tag::fromArray((array)$row);
            $tagData = $tag->toArray();
            $tagData['addon_count'] = $row->addon_count;
            $tags[] = $tagData;
        }
             
        return $tags;
    }

    /**
     * Find addons by tag
     * 
     * @param int $tagId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findAddonsByTag(int $tagId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
{
    // Get the addon IDs that have the specified tag
    $addonIds = $this->database->table('addon_tags')
        ->where('tag_id', $tagId)
        ->select('addon_id');
    
    // Find addons with those IDs
    $selection = $this->database->table('addons')
        ->where('id IN ?', $addonIds)
        ->order('name ASC');
    
    $count = $selection->count();
    $pages = (int) ceil($count / $itemsPerPage);
    
    $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
    
    $items = [];
    foreach ($selection as $row) {
        $items[] = Addon::fromArray($row->toArray());
    }
    
    return new PaginatedCollection(
        new Collection($items),
        $count,
        $page,
        $itemsPerPage,
        $pages
    );
}

    /**
     * Find tags with advanced filtering
     * 
     * @param array $filters Filtering criteria
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<Tag>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $selection = $this->getTable();
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                    $selection->where("name LIKE ?", "%{$value}%");
                    break;
                    
                case 'slug':
                    $selection->where("slug LIKE ?", "%{$value}%");
                    break;
                    
                case 'min_addons':
                    $selection->where('id IN ?', 
                        $this->database->query("
                            SELECT tag_id FROM (
                                SELECT tag_id, COUNT(*) as addon_count 
                                FROM addon_tags 
                                GROUP BY tag_id
                                HAVING addon_count >= ?
                            ) AS subquery
                        ", $value)->fetchAll()
                    );
                    break;
                    
                case 'category_id':
                    $selection->where('id IN ?', 
                        $this->database->query("
                            SELECT at.tag_id
                            FROM addon_tags at
                            JOIN addons a ON at.addon_id = a.id
                            WHERE a.category_id = ?
                            GROUP BY at.tag_id
                        ", $value)->fetchAll()
                    );
                    break;
                    
                default:
                    if (property_exists('App\Model\Tag', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Count total matching records
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Apply sorting
        if (property_exists('App\Model\Tag', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order("name ASC"); // Default sorting
        }
        
        // Apply pagination
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = Tag::fromArray($row->toArray());
        }
        
        return new PaginatedCollection(
            new Collection($items),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }

    /**
     * Find related tags
     * 
     * @param int $tagId
     * @param int $limit
     * @return array
     */
    public function findRelatedTags(int $tagId, int $limit = 10): array
    {
        // Find addons that have the specified tag
        $addonIdsQuery = $this->database->table('addon_tags')
            ->where('tag_id', $tagId)
            ->select('addon_id');
        
        // Convert the Selection to an array of IDs
        $addonIds = [];
        foreach ($addonIdsQuery as $row) {
            $addonIds[] = $row->addon_id;
        }
        
        // If no addon IDs found, return empty array
        if (empty($addonIds)) {
            return [];
        }
        
        // Find other tags used by those addons, excluding the original tag
        $result = $this->database->query("
            SELECT t.id, t.name, t.slug, COUNT(at.addon_id) as frequency
            FROM tags t
            JOIN addon_tags at ON t.id = at.tag_id
            WHERE at.addon_id IN (?) AND t.id != ?
            GROUP BY t.id, t.name, t.slug
            ORDER BY frequency DESC
            LIMIT ?
        ", $addonIds, $tagId, $limit);
        
        $relatedTags = [];
        
        foreach ($result as $row) {
            $tag = Tag::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug
            ]);
            
            $relatedTags[] = [
                'tag' => $tag,
                'frequency' => (int)$row->frequency
            ];
        }
        
        return $relatedTags;
    }

    /**
     * Get trending tags (tags with recent activity)
     * 
     * @param int $days Number of days to look back
     * @param int $limit Maximum number of tags to return
     * @return array
     */
    public function getTrendingTags(int $days = 30, int $limit = 10): array
    {
        $date = new \DateTime();
        $date->sub(new \DateInterval("P{$days}D"));
        
        // Find tags used in recently added addons
        $result = $this->database->query("
            SELECT t.id, t.name, t.slug, COUNT(at.addon_id) as usage_count
            FROM tags t
            JOIN addon_tags at ON t.id = at.tag_id
            JOIN addons a ON at.addon_id = a.id
            WHERE a.created_at >= ?
            GROUP BY t.id, t.name, t.slug
            ORDER BY usage_count DESC
            LIMIT ?
        ", $date->format('Y-m-d H:i:s'), $limit);
        
        $trendingTags = [];
        
        foreach ($result as $row) {
            $tag = Tag::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug
            ]);
            
            $trendingTags[] = [
                'tag' => $tag,
                'usage_count' => (int)$row->usage_count
            ];
        }
        
        return $trendingTags;
    }

    /**
     * Generate a weighted tag cloud
     * 
     * @param int $limit Maximum number of tags to include
     * @param int|null $categoryId Optional category ID to filter by
     * @return array
     */
    public function generateTagCloud(int $limit = 50, ?int $categoryId = null): array
    {
        $query = "
            SELECT t.id, t.name, t.slug, COUNT(at.addon_id) as weight
            FROM tags t
            JOIN addon_tags at ON t.id = at.tag_id
        ";
        
        $params = [];
        
        if ($categoryId !== null) {
            $query .= "
                JOIN addons a ON at.addon_id = a.id
                WHERE a.category_id = ?
            ";
            $params[] = $categoryId;
        }
        
        $query .= "
            GROUP BY t.id, t.name, t.slug
            ORDER BY weight DESC
            LIMIT ?
        ";
        $params[] = $limit;
        
        $result = $this->database->query($query, ...$params);
        
        $tags = [];
        $maxWeight = 0;
        $minWeight = PHP_INT_MAX;
        
        foreach ($result as $row) {
            $weight = (int)$row->weight;
            $maxWeight = max($maxWeight, $weight);
            $minWeight = min($minWeight, $weight);
            
            $tags[] = [
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'weight' => $weight
            ];
        }
        
        // Normalize weights to a scale of 1-10
        $weightRange = max(1, $maxWeight - $minWeight);
        
        foreach ($tags as &$tag) {
            $normalizedWeight = 1;
            
            if ($weightRange > 0) {
                $normalizedWeight = 1 + floor(9 * ($tag['weight'] - $minWeight) / $weightRange);
            }
            
            $tag['normalized_weight'] = (int)$normalizedWeight;
        }
        
        return $tags;
    }

    /**
     * Find tags by multiple categories
     * 
     * @param array $categoryIds
     * @param int $limit
     * @return array
     */
    public function findTagsByCategories(array $categoryIds, int $limit = 20): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        $result = $this->database->query("
            SELECT t.id, t.name, t.slug, COUNT(DISTINCT at.addon_id) as addon_count
            FROM tags t
            JOIN addon_tags at ON t.id = at.tag_id
            JOIN addons a ON at.addon_id = a.id
            WHERE a.category_id IN (?)
            GROUP BY t.id, t.name, t.slug
            ORDER BY addon_count DESC
            LIMIT ?
        ", $categoryIds, $limit);
        
        $tags = [];
        foreach ($result as $row) {
            $tag = Tag::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug
            ]);
            
            $tags[] = [
                'tag' => $tag,
                'addon_count' => (int)$row->addon_count
            ];
        }
        
        return $tags;
    }

    /**
    * Najde tagy přiřazené k doplňku
    * 
    * @param int $addonId ID doplňku
    * @return Collection<Tag>
    */
    public function findByAddon(int $addonId): Collection
    {
    $rows = $this->database->table('tags')
        ->select('tags.*')
        ->joinWhere('addon_tags', 'tags.id = addon_tags.tag_id')
        ->where('addon_tags.addon_id', $addonId);

    $tags = [];
    foreach ($rows as $row) {
        $tags[] = Tag::fromArray($row->toArray());
    }
    
    return new Collection($tags);
    }
}