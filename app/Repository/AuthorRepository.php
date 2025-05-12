<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Author;
use App\Model\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAuthorRepository;
use Nette\Database\Explorer;

/**
 * @extends BaseRepository<Author>
 * @implements AuthorRepositoryInterface
 */
class AuthorRepository extends BaseRepository implements IAuthorRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'authors';
        $this->entityClass = Author::class;
    }

    /**
     * Create a new author
     * 
     * @param Author $author
     * @return int
     */
    public function create(Author $author): int
    {
        // Set timestamps
        $author->created_at = new \DateTime();
        
        return $this->save($author);
    }

    /**
     * Find author with their addons
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array
    {
        $author = $this->findById($id);
        
        if (!$author) {
            return null;
        }
        
        $addonRows = $this->database->table('addons')
            ->where('author_id', $id)
            ->order('name ASC');
        
        $addons = [];
        foreach ($addonRows as $row) {
            $addons[] = Addon::fromArray($row->toArray());
        }
        
        return [
            'author' => $author,
            'addons' => new Collection($addons)
        ];
    }

    /**
     * Find authors with advanced filtering and sorting
     * 
     * @param array $filters Filtering criteria
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<Author>
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
                    
                case 'email':
                    $selection->where("email LIKE ?", "%{$value}%");
                    break;
                    
                case 'min_addons':
                    $selection->where('id IN ?', 
                        $this->database->query("
                            SELECT author_id FROM (
                                SELECT author_id, COUNT(*) as addon_count 
                                FROM addons 
                                GROUP BY author_id
                                HAVING addon_count >= ?
                            ) AS subquery
                        ", $value)->fetchAll()
                    );
                    break;
                    
                case 'has_website':
                    if ($value) {
                        $selection->where('website IS NOT NULL AND website != ?', '');
                    } else {
                        $selection->where('website IS NULL OR website = ?', '');
                    }
                    break;
                    
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at >= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                    
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at <= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;

                default:
                    if (property_exists('App\Model\Author', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Count total matching records
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Apply sorting
        if (property_exists('App\Model\Author', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order("name ASC"); // Default sorting
        }
        
        // Apply pagination
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = Author::fromArray($row->toArray());
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
     * Get author activity statistics
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array
    {
        $author = $this->findById($authorId);
        
        if (!$author) {
            return [];
        }
        
        // Get addon count
        $addonCount = $this->database->table('addons')
            ->where('author_id', $authorId)
            ->count();
        
        // Get total downloads
        $totalDownloads = $this->database->table('addons')
            ->where('author_id', $authorId)
            ->sum('downloads_count') ?? 0;
        
        // Get average rating
        $avgRating = $this->database->query("
            SELECT AVG(ar.rating) as avg_rating
            FROM addon_reviews ar
            JOIN addons a ON ar.addon_id = a.id
            WHERE a.author_id = ?
        ", $authorId)->fetch();
        
        // Get category distribution
        $categoryDistribution = $this->database->query("
            SELECT c.id, c.name, COUNT(a.id) as addon_count
            FROM addons a
            JOIN categories c ON a.category_id = c.id
            WHERE a.author_id = ?
            GROUP BY c.id, c.name
            ORDER BY addon_count DESC
        ", $authorId)->fetchAll();
        
        $categories = [];
        foreach ($categoryDistribution as $row) {
            $categories[] = [
                'category_id' => $row->id,
                'category_name' => $row->name,
                'addon_count' => (int)$row->addon_count
            ];
        }
        
        // Get activity timeline
        $timeline = $this->database->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as addon_count
            FROM addons
            WHERE author_id = ?
            GROUP BY month
            ORDER BY month ASC
        ", $authorId)->fetchAll();
        
        $activity = [];
        foreach ($timeline as $row) {
            $activity[] = [
                'month' => $row->month,
                'addon_count' => (int)$row->addon_count
            ];
        }
        
        return [
            'author' => $author,
            'addon_count' => (int)$addonCount,
            'total_downloads' => (int)$totalDownloads,
            'average_rating' => $avgRating ? round((float)$avgRating->avg_rating, 2) : 0,
            'category_distribution' => $categories,
            'activity_timeline' => $activity
        ];
    }

    /**
     * Get author collaboration network
     * 
     * @param int $authorId
     * @param int $depth Maximum depth of connections to explore
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array
    {
        $author = $this->findById($authorId);
        
        if (!$author) {
            return [];
        }
        
        $visitedAuthors = [$authorId => true];
        $queue = [['id' => $authorId, 'depth' => 0]];
        $network = [
            'nodes' => [
                [
                    'id' => $authorId,
                    'name' => $author->name,
                    'level' => 0
                ]
            ],
            'links' => []
        ];
        
        $i = 0;
        while ($i < count($queue)) {
            $current = $queue[$i++];
            $currentId = $current['id'];
            $currentDepth = $current['depth'];
            
            if ($currentDepth >= $depth) {
                continue;
            }
            
            // Find authors who use similar tags
            $collaborators = $this->database->query("
                SELECT DISTINCT a2.author_id, au.name, COUNT(*) as common_tags
                FROM addons a1
                JOIN addon_tags at1 ON a1.id = at1.addon_id
                JOIN addon_tags at2 ON at1.tag_id = at2.tag_id
                JOIN addons a2 ON at2.addon_id = a2.id
                JOIN authors au ON a2.author_id = au.id
                WHERE a1.author_id = ? 
                AND a2.author_id != ?
                GROUP BY a2.author_id, au.name
                HAVING common_tags > 1
                ORDER BY common_tags DESC
            ", $currentId, $currentId)->fetchAll();
            
            foreach ($collaborators as $collaborator) {
                $collaboratorId = $collaborator->author_id;
                
                // Add link
                $network['links'][] = [
                    'source' => $currentId,
                    'target' => $collaboratorId,
                    'strength' => (int)$collaborator->common_tags
                ];
                
                // Add node if not already visited
                if (!isset($visitedAuthors[$collaboratorId])) {
                    $visitedAuthors[$collaboratorId] = true;
                    
                    $network['nodes'][] = [
                        'id' => $collaboratorId,
                        'name' => $collaborator->name,
                        'level' => $currentDepth + 1
                    ];
                    
                    $queue[] = [
                        'id' => $collaboratorId,
                        'depth' => $currentDepth + 1
                    ];
                }
            }
        }
        
        return $network;
    }

    /**
     * Get top authors by various metrics
     * 
     * @param string $metric 'addons', 'downloads', or 'rating'
     * @param int $limit Maximum number of authors to return
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        $query = "";
        
        switch ($metric) {
            case 'addons':
                $query = "
                    SELECT au.id, au.name, COUNT(a.id) as addon_count
                    FROM authors au
                    JOIN addons a ON au.id = a.author_id
                    GROUP BY au.id, au.name
                    ORDER BY addon_count DESC
                    LIMIT ?
                ";
                break;
                
                case 'downloads':
                    $query = "
                        SELECT au.id, au.name, COUNT(a.id) as addon_count, SUM(a.downloads_count) as total_downloads
                        FROM authors au
                        JOIN addons a ON au.id = a.author_id
                        GROUP BY au.id, au.name
                        ORDER BY total_downloads DESC
                        LIMIT ?
                    ";
                    break;
                
            case 'rating':
                $query = "
                    SELECT au.id, au.name, AVG(a.rating) as avg_rating
                    FROM authors au
                    JOIN addons a ON au.id = a.author_id
                    WHERE a.rating > 0
                    GROUP BY au.id, au.name
                    ORDER BY avg_rating DESC
                    LIMIT ?
                ";
                break;
                
            default:
                $query = "
                    SELECT au.id, au.name, COUNT(a.id) as addon_count
                    FROM authors au
                    JOIN addons a ON au.id = a.author_id
                    GROUP BY au.id, au.name
                    ORDER BY addon_count DESC
                    LIMIT ?
                ";
        }
        
        $result = $this->database->query($query, $limit);
        
        $authors = [];
        foreach ($result as $row) {
            $author = Author::fromArray([
                'id' => $row->id,
                'name' => $row->name,
                'email' => null,
                'website' => null,
                'created_at' => new \DateTime()
            ]);
            
            $data = [
                'author' => $author
            ];
            
            if (isset($row->addon_count)) {
                $data['addon_count'] = (int)$row->addon_count;
            }
            
            if (isset($row->total_downloads)) {
                $data['total_downloads'] = (int)$row->total_downloads;
            }
            
            if (isset($row->avg_rating)) {
                $data['average_rating'] = round((float)$row->avg_rating, 2);
            }
            
            $authors[] = $data;
        }
        
        return $authors;
    }
}