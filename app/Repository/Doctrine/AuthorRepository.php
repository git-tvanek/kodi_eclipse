<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Author;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends BaseDoctrineRepository<Author>
 */
class AuthorRepository extends BaseDoctrineRepository implements IAuthorRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Author::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function create(Author $author): int
    {
        $this->entityManager->persist($author);
        $this->entityManager->flush();
        
        return $author->getId();
    }
    
    public function getWithAddons(int $id): ?array
    {
        $author = $this->find($id);
        
        if (!$author) {
            return null;
        }
        
        // Load addons
        $addons = $author->getAddons();
        
        return [
            'author' => $author,
            'addons' => new Collection($addons->toArray())
        ];
    }
    
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                    $qb->andWhere('a.name LIKE :name')
                       ->setParameter('name', '%' . $value . '%');
                    break;
                
                case 'email':
                    $qb->andWhere('a.email LIKE :email')
                       ->setParameter('email', '%' . $value . '%');
                    break;
                
                case 'min_addons':
                    $qb->join('a.addons', 'addon')
                       ->groupBy('a.id')
                       ->having('COUNT(addon.id) >= :minAddons')
                       ->setParameter('minAddons', $value);
                    break;
                
                case 'has_website':
                    if ($value) {
                        $qb->andWhere('a.website IS NOT NULL')
                           ->andWhere('a.website != :emptyString')
                           ->setParameter('emptyString', '');
                    } else {
                        $qb->andWhere('a.website IS NULL OR a.website = :emptyString')
                           ->setParameter('emptyString', '');
                    }
                    break;
                
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('a.created_at >= :createdAfter')
                           ->setParameter('createdAfter', $value);
                    }
                    break;
                
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('a.created_at <= :createdBefore')
                           ->setParameter('createdBefore', $value);
                    }
                    break;
                
                default:
                    if (property_exists(Author::class, $key)) {
                        $qb->andWhere("a.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(Author::class, $sortBy)) {
            $qb->orderBy("a.$sortBy", $sortDir);
        } else {
            $qb->orderBy('a.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function getAuthorStatistics(int $authorId): array
    {
        $author = $this->find($authorId);
        
        if (!$author) {
            return [];
        }
        
        $addons = $author->getAddons();
        
        // Calculate addon count
        $addonCount = count($addons);
        
        // Calculate total downloads
        $totalDownloads = 0;
        $ratings = [];
        $categoryDistribution = [];
        $activityTimeline = [];
        
        foreach ($addons as $addon) {
            $totalDownloads += $addon->getDownloadsCount();
            $ratings[] = $addon->getRating();
            
            $categoryId = $addon->getCategory()->getId();
            $categoryName = $addon->getCategory()->getName();
            
            if (!isset($categoryDistribution[$categoryId])) {
                $categoryDistribution[$categoryId] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'addon_count' => 0
                ];
            }
            
            $categoryDistribution[$categoryId]['addon_count']++;
            
            // Group addons by month
            $month = $addon->getCreatedAt()->format('Y-m');
            if (!isset($activityTimeline[$month])) {
                $activityTimeline[$month] = [
                    'month' => $month,
                    'addon_count' => 0
                ];
            }
            
            $activityTimeline[$month]['addon_count']++;
        }
        
        // Calculate average rating
        $avgRating = 0;
        if (!empty($ratings)) {
            $avgRating = array_sum($ratings) / count($ratings);
        }
        
        return [
            'author' => $author,
            'addon_count' => $addonCount,
            'total_downloads' => $totalDownloads,
            'average_rating' => round($avgRating, 2),
            'category_distribution' => array_values($categoryDistribution),
            'activity_timeline' => array_values($activityTimeline)
        ];
    }
    
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array
    {
        $author = $this->find($authorId);
        
        if (!$author) {
            return [];
        }
        
        // Initialize network with the author
        $visitedAuthors = [$authorId => true];
        $network = [
            'nodes' => [
                [
                    'id' => $authorId,
                    'name' => $author->getName(),
                    'level' => 0
                ]
            ],
            'links' => []
        ];
        
        // Find authors who use similar tags
        $this->buildCollaborationNetwork($author, $visitedAuthors, $network, 0, $depth);
        
        return $network;
    }
    
    private function buildCollaborationNetwork(Author $author, array &$visitedAuthors, array &$network, int $level, int $maxDepth): void
    {
        if ($level >= $maxDepth) {
            return;
        }
        
        // Get all tags used by this author's addons
        $authorTags = [];
        foreach ($author->getAddons() as $addon) {
            foreach ($addon->getTags() as $tag) {
                $authorTags[] = $tag->getId();
            }
        }
        
        // Skip if author has no tags
        if (empty($authorTags)) {
            return;
        }
        
        // Find other authors who use the same tags
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a', 'COUNT(DISTINCT t.id) as tagCount')
           ->from(Author::class, 'a')
           ->join('a.addons', 'addon')
           ->join('addon.tags', 't')
           ->where('t.id IN (:tags)')
           ->andWhere('a.id != :authorId')
           ->setParameter('tags', array_unique($authorTags))
           ->setParameter('authorId', $author->getId())
           ->groupBy('a.id')
           ->having('tagCount > 1')
           ->orderBy('tagCount', 'DESC');
        
        $result = $qb->getQuery()->getResult();
        
        foreach ($result as $row) {
            $collaborator = $row[0];
            $tagCount = $row['tagCount'];
            $collaboratorId = $collaborator->getId();
            
            // Add link
            $network['links'][] = [
                'source' => $author->getId(),
                'target' => $collaboratorId,
                'strength' => (int)$tagCount
            ];
            
            // Add node if not already visited
            if (!isset($visitedAuthors[$collaboratorId])) {
                $visitedAuthors[$collaboratorId] = true;
                
                $network['nodes'][] = [
                    'id' => $collaboratorId,
                    'name' => $collaborator->getName(),
                    'level' => $level + 1
                ];
                
                // Recursively build the network
                $this->buildCollaborationNetwork($collaborator, $visitedAuthors, $network, $level + 1, $maxDepth);
            }
        }
    }
    
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a');
        
        switch ($metric) {
            case 'downloads':
                $qb->addSelect('SUM(addon.downloads_count) as metricValue')
                   ->from(Author::class, 'a')
                   ->join('a.addons', 'addon')
                   ->groupBy('a.id')
                   ->orderBy('metricValue', 'DESC');
                break;
                
            case 'rating':
                $qb->addSelect('AVG(addon.rating) as metricValue')
                   ->from(Author::class, 'a')
                   ->join('a.addons', 'addon')
                   ->groupBy('a.id')
                   ->orderBy('metricValue', 'DESC');
                break;
                
            case 'addons':
            default:
                $qb->addSelect('COUNT(addon.id) as metricValue')
                   ->from(Author::class, 'a')
                   ->join('a.addons', 'addon')
                   ->groupBy('a.id')
                   ->orderBy('metricValue', 'DESC');
                break;
        }
        
        $qb->setMaxResults($limit);
        $result = $qb->getQuery()->getResult();
        
        $authors = [];
        foreach ($result as $row) {
            $author = $row[0];
            $value = $row['metricValue'];
            
            $data = [
                'author' => $author
            ];
            
            switch ($metric) {
                case 'downloads':
                    $data['total_downloads'] = (int)$value;
                    break;
                    
                case 'rating':
                    $data['average_rating'] = round((float)$value, 2);
                    break;
                    
                case 'addons':
                default:
                    $data['addon_count'] = (int)$value;
                    break;
            }
            
            $authors[] = $data;
        }
        
        return $authors;
    }
}