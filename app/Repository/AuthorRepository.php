<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Repozitář pro práci s autory doplňků
 * 
 * @extends BaseRepository<Author>
 */
class AuthorRepository extends BaseRepository implements IAuthorRepository
{
    protected string $defaultAlias = 'a';
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Author::class);
    }
    
    /**
     * Vytvoří typovanou kolekci autorů
     * 
     * @param array<Author> $entities
     * @return Collection<Author>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Vytvoří nového autora
     * 
     * @param Author $author
     * @return int ID vytvořeného autora
     */
    public function create(Author $author): int
    {
        return $this->save($author);
    }
    
    /**
     * Vrátí autora s jeho doplňky
     * 
     * @param int $id ID autora
     * @return array|null Pole obsahující autora a jeho doplňky, nebo null pokud autor neexistuje
     */
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
            'addons' => $this->createCollection($addons->toArray())
        ];
    }
    
    /**
     * Vyhledá autory podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Author> Stránkovaná kolekce autorů
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        // Apply standard filters from parent
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        // Apply custom filters specific to authors
        if (isset($filters['min_addons']) && $filters['min_addons'] !== null && (int)$filters['min_addons'] > 0) {
            $qb->join("$this->defaultAlias.addons", 'addon')
               ->groupBy("$this->defaultAlias.id")
               ->having('COUNT(addon.id) >= :minAddons')
               ->setParameter('minAddons', (int)$filters['min_addons']);
        }
        
        if (isset($filters['has_website']) && $filters['has_website'] !== null) {
            if ($filters['has_website']) {
                $qb->andWhere("$this->defaultAlias.website IS NOT NULL")
                   ->andWhere("$this->defaultAlias.website != :emptyString")
                   ->setParameter('emptyString', '');
            } else {
                $qb->andWhere("$this->defaultAlias.website IS NULL OR $this->defaultAlias.website = :emptyString")
                   ->setParameter('emptyString', '');
            }
        }
        
        // Apply sorting
        if ($this->hasProperty($sortBy)) {
            $qb->orderBy("$this->defaultAlias.$sortBy", $sortDir);
        } else {
            $qb->orderBy("$this->defaultAlias.name", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Získá statistiky autora a jeho doplňků
     * 
     * @param int $authorId ID autora
     * @return array Statistiky autora nebo prázdné pole, pokud autor neexistuje
     */
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
    
    /**
     * Vrátí autory seřazené podle zvoleného kritéria
     * 
     * @param string $metric Kritérium ('addons', 'downloads', 'rating')
     * @param int $limit Maximální počet autorů
     * @return array Pole s nejlepšími autory podle zvoleného kritéria
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select("$this->defaultAlias");
        
        switch ($metric) {
            case 'downloads':
                $qb->addSelect('SUM(addon.downloads_count) as metricValue')
                   ->from(Author::class, $this->defaultAlias)
                   ->join("$this->defaultAlias.addons", 'addon')
                   ->groupBy("$this->defaultAlias.id")
                   ->orderBy('metricValue', 'DESC');
                break;
                
            case 'rating':
                $qb->addSelect('AVG(addon.rating) as metricValue')
                   ->from(Author::class, $this->defaultAlias)
                   ->join("$this->defaultAlias.addons", 'addon')
                   ->groupBy("$this->defaultAlias.id")
                   ->orderBy('metricValue', 'DESC');
                break;
                
            case 'addons':
            default:
                $qb->addSelect('COUNT(addon.id) as metricValue')
                   ->from(Author::class, $this->defaultAlias)
                   ->join("$this->defaultAlias.addons", 'addon')
                   ->groupBy("$this->defaultAlias.id")
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
    
    /**
     * Vytvoří síť autorů spolupracujících prostřednictvím podobných tagů
     * 
     * @param int $authorId ID autora
     * @param int $depth Hloubka prohledávání sítě
     * @return array Struktura sítě spolupracujících autorů
     */
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
    
    /**
     * Pomocná metoda pro rekurzivní vytváření sítě spolupracujících autorů
     * 
     * @param Author $author Autor
     * @param array &$visitedAuthors Reference na pole již navštívených autorů
     * @param array &$network Reference na síť autorů
     * @param int $level Aktuální úroveň hloubky
     * @param int $maxDepth Maximální hloubka prohledávání
     */
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
        $qb->select($this->defaultAlias, 'COUNT(DISTINCT t.id) as tagCount')
           ->from(Author::class, $this->defaultAlias)
           ->join("$this->defaultAlias.addons", 'addon')
           ->join('addon.tags', 't')
           ->where('t.id IN (:tags)')
           ->andWhere("$this->defaultAlias.id != :authorId")
           ->setParameter('tags', array_unique($authorTags))
           ->setParameter('authorId', $author->getId())
           ->groupBy("$this->defaultAlias.id")
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
}