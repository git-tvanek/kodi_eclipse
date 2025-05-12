<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Addon;
use App\Model\Screenshot;
use App\Model\Tag;
use App\Model\AddonTag;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAddonRepository;
use App\Repository\Query\AddonAdvancedSearchQuery;
use App\Repository\Query\AddonFilterQuery;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

/**
 * @extends BaseRepository<Addon>
 * @implements AddonRepositoryInterface
 */
class AddonRepository extends BaseRepository implements IAddonRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'addons';
        $this->entityClass = Addon::class;
    }

    /**
     * Find addon by slug
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon
    {
        /** @var Addon|null */
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Find addons by category
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->findWithPagination(['category_id' => $categoryId], $page, $itemsPerPage, 'name', 'ASC');
    }

    /**
     * Find addons in a category and all its subcategories
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategoryRecursive(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Get the category repository
        $categoryRepository = new CategoryRepository($this->database);
        
        // Get all subcategories including the parent category
        $subcategories = $categoryRepository->findAllSubcategoriesRecursive($categoryId);
        $categoryIds = [];
        
        foreach ($subcategories as $category) {
            $categoryIds[] = $category->id;
        }
        
        $categoryIds[] = $categoryId; // Include the parent category
        
        // Find addons in all categories
        return $this->findWithPagination(['category_id' => $categoryIds], $page, $itemsPerPage, 'name', 'ASC');
    }

    /**
     * Find addons by author
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->findWithPagination(['author_id' => $authorId], $page, $itemsPerPage, 'name', 'ASC');
    }

    /**
     * Find popular addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findPopular(int $limit = 10): Collection
    {
        $rows = $this->findAll()->order('downloads_count DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return new Collection($addons);
    }

    /**
     * Find top rated addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findTopRated(int $limit = 10): Collection
    {
        $rows = $this->findAll()->order('rating DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return new Collection($addons);
    }

    /**
     * Find newest addons
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findNewest(int $limit = 10): Collection
    {
        $rows = $this->findAll()->order('created_at DESC')->limit($limit);
        $addons = [];
        
        foreach ($rows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return new Collection($addons);
    }

    /**
     * Search addons by keyword
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $selection = $this->getTable()
            ->where('name LIKE ? OR description LIKE ?', 
                "%{$query}%", "%{$query}%");
        
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        $items = [];
        foreach ($selection as $row) {
            $items[] = Addon::fromArray($row->toArray());
        }
        
        $collection = new Collection($items);
        
        return new PaginatedCollection(
            $collection,
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }

    /**
     * Increment addon download count
     * 
     * @param int $id
     * @return int
     */
    public function incrementDownloadCount(int $id): int
    {
        return $this->getTable()->where('id', $id)->update([
            'downloads_count' => new \Nette\Database\SqlLiteral('downloads_count + 1')
        ]);
    }

    /**
     * Update addon rating
     * 
     * @param int $id
     */
    public function updateRating(int $id): void
    {
        $averageRating = $this->database->table('addon_reviews')
            ->where('addon_id', $id)
            ->select('AVG(rating) AS average_rating')
            ->fetch();

        if ($averageRating && $averageRating->average_rating) {
            $addon = $this->findById($id);
            if ($addon) {
                $addon->rating = (float) $averageRating->average_rating;
                $this->save($addon);
            }
        }
    }

    /**
     * Create addon with related data
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @return int
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        // Generate slug if not provided
        if (empty($addon->slug)) {
            $addon->slug = Strings::webalize($addon->name);
        }
        
        // Set timestamps
        $addon->created_at = new \DateTime();
        $addon->updated_at = new \DateTime();
        
        // Begin transaction
        $this->database->beginTransaction();
        
        try {
            // Insert addon
            $addonId = $this->save($addon);
            
            // Insert screenshots
            if (!empty($screenshots)) {
                foreach ($screenshots as $index => $screenshot) {
                    $screenshot->addon_id = $addonId;
                    $screenshot->sort_order = $index;
                    $this->database->table('screenshots')->insert($screenshot->toArray());
                }
            }
            
            // Insert tags
            if (!empty($tagIds)) {
                foreach ($tagIds as $tagId) {
                    $addonTag = new AddonTag();
                    $addonTag->addon_id = $addonId;
                    $addonTag->tag_id = $tagId;
                    $this->database->table('addon_tags')->insert($addonTag->toArray());
                }
            }
            
            // Commit transaction
            $this->database->commit();
            
            return $addonId;
        } catch (\Exception $e) {
            // Rollback on error
            $this->database->rollBack();
            throw $e;
        }
    }

/**
 * Update addon with related data
 * 
 * @param Addon $addon
 * @param array $screenshots
 * @param array $tagIds
 * @return int
 */
public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
{
    // Ensure addon exists
    if (!$this->exists($addon->id)) {
        throw new \Exception("Addon with ID {$addon->id} does not exist.");
    }
    
    // Handle file uploads (if any)
    if (isset($addon->icon_url) && $addon->icon_url instanceof FileUpload && $addon->icon_url->isOk()) {
        $iconPath = $this->processImageUpload($addon->icon_url, 'icons');
        $addon->icon_url = $iconPath;
    }
    
    if (isset($addon->fanart_url) && $addon->fanart_url instanceof FileUpload && $addon->fanart_url->isOk()) {
        $fanartPath = $this->processImageUpload($addon->fanart_url, 'fanart');
        $addon->fanart_url = $fanartPath;
    }
    
    // Update slug if name changed
    if (empty($addon->slug)) {
        $addon->slug = Strings::webalize($addon->name);
    }
    
    // Set updated timestamp
    $addon->updated_at = new \DateTime();
    
    // Begin transaction
    $this->database->beginTransaction();
    
    try {
        // Update addon
        $this->save($addon);
        
        // Handle screenshots
        if (!empty($screenshots)) {
            // Remove existing screenshots
            $this->database->table('screenshots')->where('addon_id', $addon->id)->delete();
            
            // Add new screenshots
            foreach ($screenshots as $index => $screenshot) {
                $screenshot->addon_id = $addon->id;
                $screenshot->sort_order = $index;
                $this->database->table('screenshots')->insert($screenshot->toArray());
            }
        }
        
        // Handle tags
        if (!empty($tagIds)) {
            // Remove existing tag associations
            $this->database->table('addon_tags')->where('addon_id', $addon->id)->delete();
            
            // Add new tag associations
            foreach ($tagIds as $tagId) {
                $addonTag = new AddonTag();
                $addonTag->addon_id = $addon->id;
                $addonTag->tag_id = $tagId;
                $this->database->table('addon_tags')->insert($addonTag->toArray());
            }
        }
        
        // Commit transaction
        $this->database->commit();
        
        return $addon->id;
    } catch (\Exception $e) {
        // Rollback on error
        $this->database->rollBack();
        throw $e;
    }
}
    
    /**
     * Get addon with related data
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithRelated(int $id): ?array
    {
        $addon = $this->findById($id);
        
        if (!$addon) {
            return null;
        }
        
        // Get author
        $authorRow = $this->database->table('authors')
            ->get($addon->author_id);
        $author = $authorRow ? \App\Model\Author::fromArray($authorRow->toArray()) : null;
        
        // Get category
        $categoryRow = $this->database->table('categories')
            ->get($addon->category_id);
        $category = $categoryRow ? \App\Model\Category::fromArray($categoryRow->toArray()) : null;
        
        // Get screenshots
        $screenshotRows = $this->database->table('screenshots')
            ->where('addon_id', $id)
            ->order('sort_order ASC');
        
        $screenshots = [];
        foreach ($screenshotRows as $screenshotRow) {
            $screenshots[] = \App\Model\Screenshot::fromArray($screenshotRow->toArray());
        }
        
       // Get tags
        $tagIds = $this->database->table('addon_tags')
            ->where('addon_id', $id)
            ->select('tag_id');

        $tagRows = $this->database->table('tags')
            ->where('id IN ?', $tagIds);

        $tags = [];
        foreach ($tagRows as $tagRow) {
            $tags[] = \App\Model\Tag::fromArray($tagRow->toArray());
            }
        
        // Get reviews
        $reviewRows = $this->database->table('addon_reviews')
            ->where('addon_id', $id)
            ->order('created_at DESC');
        
        $reviews = [];
        foreach ($reviewRows as $reviewRow) {
            $reviews[] = \App\Model\AddonReview::fromArray($reviewRow->toArray());
        }
        
        return [
            'addon' => $addon,
            'author' => $author,
            'category' => $category,
            'screenshots' => $screenshots,
            'tags' => $tags,
            'reviews' => $reviews
        ];
    }

    /**
     * Find addons with advanced filtering and sorting
     * 
     * @param array $filters Filtering criteria
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<Addon>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $filterQuery = new AddonFilterQuery(
            $this->database,
            $filters,
            $sortBy,
            $sortDir,
            $page,
            $itemsPerPage
        );
        
        return $filterQuery->execute();
    }

    /**
     * Find similar addons based on tags and category
     * 
     * @param int $addonId
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        $addon = $this->findById($addonId);
        if (!$addon) {
            return new Collection();
        }

        // Get the addon's tags
        $addonTags = $this->database->table('addon_tags')
            ->where('addon_id', $addonId)
            ->select('tag_id');

        $tagIds = [];
        foreach ($addonTags as $tag) {
            $tagIds[] = $tag->tag_id;
        }

        // If no tags found, fallback to category only
        if (empty($tagIds)) {
            $result = $this->findWithFilters([
                'category_id' => $addon->category_id,
                'id != ?' => $addonId
            ], 'downloads_count', 'DESC', 1, $limit);
            
            return $result->getItems();
        }

        // Find addons with similar tags and same category
        $query = "
            SELECT s.*
            FROM (
                SELECT a.*, 
                        COUNT(DISTINCT at.tag_id) AS matching_tags,
                        (a.category_id = ?) AS same_category
            FROM addons a
                JOIN addon_tags at ON a.id = at.addon_id
                WHERE at.tag_id IN (?) AND a.id != ?
                GROUP BY a.id
                ) AS s
                ORDER BY (s.same_category * 2 + s.matching_tags) DESC, s.downloads_count DESC
                LIMIT ?
                ";

        $result = $this->database->query($query, $addon->category_id, $tagIds, $addonId, $limit);

        $similarAddons = [];
        foreach ($result as $row) {
            $similarAddons[] = Addon::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'slug' => $row->slug,
                'description' => $row->description,
                'version' => $row->version,
                'author_id' => $row->author_id,
                'category_id' => $row->category_id,
                'repository_url' => $row->repository_url,
                'download_url' => $row->download_url,
                'icon_url' => $row->icon_url,
                'fanart_url' => $row->fanart_url,
                'kodi_version_min' => $row->kodi_version_min,
                'kodi_version_max' => $row->kodi_version_max,
                'downloads_count' => $row->downloads_count,
                'rating' => $row->rating,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ]);
        }

        return new Collection($similarAddons);
    }
    
    /**
     * Comprehensive search with relevance sorting
     * 
     * @param string $query The search query
     * @param array $fields Fields to search in (default: name, description)
     * @param array $filters Additional filters to apply
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<Addon>
     */
    public function advancedSearch(string $query, array $fields = ['name', 'description'], array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $searchQuery = new AddonAdvancedSearchQuery(
            $this->database,
            $query,
            $fields,
            $filters,
            $page,
            $itemsPerPage
        );
        
        return $searchQuery->execute();
    }
    
    /**
     * Perform full-text search
     * 
     * @param string $query Search query
     * @param array $fields Fields to search in
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<Addon>
     */
    public function fullTextSearch(string $query, array $fields = ['name', 'description'], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Pokud je dotaz prázdný, vrátit prázdný výsledek
        if (empty(trim($query))) {
            return new PaginatedCollection(
                new Collection([]),
                0,
                $page,
                $itemsPerPage,
                0
            );
        }
        
        // Pro SQLite implementujeme jednoduché vyhledávání založené na LIKE
        // V produkčním prostředí s MySQL byste mohli použít MATCH AGAINST
        $selection = $this->getTable();
        $conditions = [];
        $params = [];
        
        // Rozdělit dotaz na klíčová slova
        $keywords = preg_split('/\s+/', trim($query));
        
        foreach ($fields as $field) {
            foreach ($keywords as $keyword) {
                $conditions[] = "$field LIKE ?";
                $params[] = "%{$keyword}%";
            }
        }
        
        if (!empty($conditions)) {
            $selection->where(implode(' OR ', $conditions), ...$params);
        }
        
        // Spočítat celkový počet odpovídajících záznamů
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Aplikovat stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Převést na entity
        $items = [];
        foreach ($selection as $row) {
            $items[] = Addon::fromArray($row->toArray());
        }
        
        // Vytvořit a vrátit typovanou kolekci s paginací
        return new PaginatedCollection(
            new Collection($items),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }

    /**
     * Get addon statistics over time
     * 
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Number of intervals to return
     * @param string $metric 'downloads', 'ratings', or 'addons'
     * @return array
     */
    public function getStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array
    {
        $result = [];
        $now = new \DateTime();
        
        // Define SQL date format based on interval
        switch ($interval) {
            case 'day':
                $sqlFormat = '%Y-%m-%d';
                $dateFormat = 'Y-m-d';
                $dateInterval = 'P1D';
                break;
            case 'week':
                $sqlFormat = '%Y-%u'; // Year and week number
                $dateFormat = 'Y-W';
                $dateInterval = 'P1W';
                break;
            case 'month':
                $sqlFormat = '%Y-%m';
                $dateFormat = 'Y-m';
                $dateInterval = 'P1M';
                break;
            case 'year':
                $sqlFormat = '%Y';
                $dateFormat = 'Y';
                $dateInterval = 'P1Y';
                break;
            default:
                $sqlFormat = '%Y-%m';
                $dateFormat = 'Y-m';
                $dateInterval = 'P1M';
        }
        
        // Generate time periods
        $periods = [];
        $currentDate = clone $now;
        
        for ($i = 0; $i < $limit; $i++) {
            $periods[] = $currentDate->format($dateFormat);
            $currentDate->sub(new \DateInterval($dateInterval));
        }
        
        // Sort periods chronologically
        $periods = array_reverse($periods);
        
        // Initialize result structure
        foreach ($periods as $period) {
            $result[$period] = [
                'period' => $period,
                'value' => 0
            ];
        }
        
        // Query the database based on the requested metric
        if ($metric === 'addons') {
            // Count new addons per period
            $query = $this->database->query("
                SELECT DATE_FORMAT(created_at, '$sqlFormat') AS period, COUNT(*) AS count
                FROM {$this->tableName}
                WHERE created_at >= ?
                GROUP BY period
                ORDER BY period
            ", $currentDate->format('Y-m-d H:i:s'));
            
            foreach ($query as $row) {
                if (isset($result[$row->period])) {
                    $result[$row->period]['value'] = (int)$row->count;
                }
            }
        } elseif ($metric === 'ratings') {
            // Average ratings per period
            $query = $this->database->query("
                SELECT DATE_FORMAT(ar.created_at, '$sqlFormat') AS period, AVG(ar.rating) AS avg_rating
                FROM addon_reviews ar
                WHERE ar.created_at >= ?
                GROUP BY period
                ORDER BY period
            ", $currentDate->format('Y-m-d H:i:s'));
            
            foreach ($query as $row) {
                if (isset($result[$row->period])) {
                    $result[$row->period]['value'] = round((float)$row->avg_rating, 2);
                }
            }
        } else {
            // Download count changes are not directly tracked over time in this schema
            // For demo purposes, we'll generate random data (in a real app, you'd have a downloads log table)
            foreach ($result as $period => &$data) {
                $data['value'] = rand(50, 500); // Random download count
            }
        }
        
        return array_values($result);
    }

    /**
     * Get addon distribution by category
     * 
     * @return array
     */
    public function getAddonDistributionByCategory(): array
    {
        $result = $this->database->query("
            SELECT c.id, c.name, COUNT(a.id) as addon_count
            FROM categories c
            LEFT JOIN {$this->tableName} a ON c.id = a.category_id
            GROUP BY c.id, c.name
            ORDER BY addon_count DESC
        ");
        
        $distribution = [];
        
        foreach ($result as $row) {
            $distribution[] = [
                'category_id' => $row->id,
                'category_name' => $row->name,
                'addon_count' => (int)$row->addon_count
            ];
        }
        
        return $distribution;
    }

    /**
     * Get rating distribution
     * 
     * @return array
     */
    public function getRatingDistribution(): array
    {
        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];
        
        $result = $this->database->query("
            SELECT rating, COUNT(*) as count
            FROM addon_reviews
            GROUP BY rating
            ORDER BY rating
        ");
        
        foreach ($result as $row) {
            $rating = (int)$row->rating;
            if (isset($distribution[$rating])) {
                $distribution[$rating] = (int)$row->count;
            }
        }
        
        return $distribution;
    }

    /**
     * Get top authors by download count
     * 
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array
    {
        $result = $this->database->query("
            SELECT au.id, au.name, COUNT(a.id) as addon_count, SUM(a.downloads_count) as total_downloads
            FROM authors au
            JOIN {$this->tableName} a ON au.id = a.author_id
            GROUP BY au.id, au.name
            ORDER BY total_downloads DESC
            LIMIT ?
        ", $limit);
        
        $authors = [];
        
        foreach ($result as $row) {
            $authors[] = [
                'author_id' => $row->id,
                'author_name' => $row->name,
                'addon_count' => (int)$row->addon_count,
                'total_downloads' => (int)$row->total_downloads
            ];
        }
        
        return $authors;
    }

    
}