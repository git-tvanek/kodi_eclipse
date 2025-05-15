<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Collection\Collection;
use App\Collection\AddonCollection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAddonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Repozitář pro práci s doplňky
 * 
 * @extends BaseDoctrineRepository<Addon>
 */
class AddonRepository extends BaseDoctrineRepository implements IAddonRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Addon::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new AddonCollection($entities);
    }
    
    // -------------------------------------------------------------------------
    // ZÁKLADNÍ VYHLEDÁVACÍ METODY
    // -------------------------------------------------------------------------
    
    /**
     * Najde doplněk podle slugu
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    /**
     * Najde doplňky podle autora
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.author = :author')
            ->setParameter('author', $this->entityManager->getReference(Author::class, $authorId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    // -------------------------------------------------------------------------
    // METODY SOUVISEJÍCÍ S KATEGORIEMI
    // -------------------------------------------------------------------------
    
    /**
     * Najde doplňky v konkrétní kategorii
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.category = :category')
            ->setParameter('category', $this->entityManager->getReference(Category::class, $categoryId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Najde doplňky v kategorii a všech jejích podkategoriích
     */
    public function findByCategoryRecursive(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Get all subcategory IDs
        $categoryIds = [$categoryId];
        $this->findAllSubcategoryIds($categoryId, $categoryIds);
        
        // Find addons in all categories
        $qb = $this->createQueryBuilder('a')
            ->where('a.category IN (:categories)')
            ->setParameter('categories', array_map(
                fn($id) => $this->entityManager->getReference(Category::class, $id),
                $categoryIds
            ))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pomocná metoda pro rekurzivní hledání ID všech podkategorií
     */
    private function findAllSubcategoryIds(int $parentId, array &$categoryIds): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $subcategories = $qb->select('c.id')
            ->from(Category::class, 'c')
            ->where('c.parent = :parent')
            ->setParameter('parent', $this->entityManager->getReference(Category::class, $parentId))
            ->getQuery()
            ->getResult();
        
        foreach ($subcategories as $subcategory) {
            $subId = $subcategory['id'];
            $categoryIds[] = $subId;
            $this->findAllSubcategoryIds($subId, $categoryIds);
        }
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO STATISTIKY A DOPORUČENÍ
    // -------------------------------------------------------------------------
    
    /**
     * Najde nejstahovanější doplňky
     */
    public function findPopular(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.downloads_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde nejlépe hodnocené doplňky
     */
    public function findTopRated(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde nejnovější doplňky
     */
    public function findNewest(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new AddonCollection($addons);
    }
    
    /**
     * Najde podobné doplňky k zadanému doplňku
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        $addon = $this->find($addonId);
        if (!$addon) {
            return new AddonCollection([]);
        }
        
        $category = $addon->getCategory();
        $tags = $addon->getTags();
        
        $qb = $this->createQueryBuilder('a')
            ->where('a.id != :addonId')
            ->setParameter('addonId', $addonId);
        
        // If addon has tags, use them for finding similar addons
        if (count($tags) > 0) {
            $qb->join('a.tags', 't')
               ->andWhere('t IN (:tags)')
               ->andWhere('a.category = :category')
               ->setParameter('tags', $tags)
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->setMaxResults($limit);
        } else {
            // Otherwise just use category
            $qb->andWhere('a.category = :category')
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->setMaxResults($limit);
        }
        
        $similarAddons = $qb->getQuery()->getResult();
        return new AddonCollection($similarAddons);
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO VYHLEDÁVÁNÍ A FILTROVÁNÍ
    // -------------------------------------------------------------------------
    
    /**
     * Vyhledá doplňky podle klíčového slova
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.name LIKE :query')
            ->orWhere('a.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pokročilé vyhledávání s možností filtrování
     */
    public function advancedSearch(string $query, array $fields = ['name', 'description'], array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a');
        
        // Add search query if not empty
        if (!empty($query)) {
            $orExpressions = $qb->expr()->orX();
            foreach ($fields as $field) {
                $orExpressions->add($qb->expr()->like("a.$field", ":query"));
            }
            $qb->andWhere($orExpressions)
               ->setParameter('query', '%' . $query . '%');
        }
        
        // Apply filters
        $this->applyFilters($qb, $filters);
        
        // Apply sorting
        if (!empty($query)) {
            // For text search, sort by name
            $qb->orderBy('a.name', 'ASC');
        } else if (isset($filters['sort_by'])) {
            $qb->orderBy('a.' . $filters['sort_by'], $filters['sort_dir'] ?? 'ASC');
        } else {
            // Default sorting by downloads
            $qb->orderBy('a.downloads_count', 'DESC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pomocná metoda pro aplikaci filtrů na dotaz
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '' || $key === 'sort_by' || $key === 'sort_dir') {
                continue;
            }
            
            switch ($key) {
                case 'category_ids':
                    if (is_array($value) && !empty($value)) {
                        $categories = array_map(
                            fn($id) => $this->entityManager->getReference(Category::class, $id),
                            $value
                        );
                        $qb->andWhere('a.category IN (:categories)')
                           ->setParameter('categories', $categories);
                    } else if (!is_array($value)) {
                        $qb->andWhere('a.category = :category')
                           ->setParameter('category', $this->entityManager->getReference(Category::class, $value));
                    }
                    break;
                
                case 'author_ids':
                    if (is_array($value) && !empty($value)) {
                        $authors = array_map(
                            fn($id) => $this->entityManager->getReference(Author::class, $id),
                            $value
                        );
                        $qb->andWhere('a.author IN (:authors)')
                           ->setParameter('authors', $authors);
                    } else if (!is_array($value)) {
                        $qb->andWhere('a.author = :author')
                           ->setParameter('author', $this->entityManager->getReference(Author::class, $value));
                    }
                    break;
                
                case 'tag_ids':
                    if (is_array($value) && !empty($value)) {
                        $tags = array_map(
                            fn($id) => $this->entityManager->getReference(Tag::class, $id),
                            $value
                        );
                        $qb->join('a.tags', 'tag')
                           ->andWhere('tag IN (:tags)')
                           ->setParameter('tags', $tags);
                    }
                    break;
                
                case 'min_rating':
                    $qb->andWhere('a.rating >= :minRating')
                       ->setParameter('minRating', $value);
                    break;
                
                case 'max_rating':
                    $qb->andWhere('a.rating <= :maxRating')
                       ->setParameter('maxRating', $value);
                    break;
                
                case 'min_downloads':
                    $qb->andWhere('a.downloads_count >= :minDownloads')
                       ->setParameter('minDownloads', $value);
                    break;
                
                case 'max_downloads':
                    $qb->andWhere('a.downloads_count <= :maxDownloads')
                       ->setParameter('maxDownloads', $value);
                    break;
                
                case 'kodi_version':
                    $qb->andWhere('(a.kodi_version_min IS NULL OR a.kodi_version_min <= :kodiVersion)')
                       ->andWhere('(a.kodi_version_max IS NULL OR a.kodi_version_max >= :kodiVersion)')
                       ->setParameter('kodiVersion', $value);
                    break;
                
                default:
                    // For other standard fields
                    if (property_exists(Addon::class, $key)) {
                        $qb->andWhere("a.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO AKTUALIZACI A MANIPULACI S DATY
    // -------------------------------------------------------------------------
    
/**
 * Zvýší počet stažení doplňku a zaloguje stažení
 * 
 * @param int $id ID doplňku
 * @param string|null $ipAddress IP adresa uživatele (volitelné)
 * @param string|null $userAgent User agent uživatele (volitelné)
 * @return int
 */
public function incrementDownloadCount(int $id, ?string $ipAddress = null, ?string $userAgent = null): int
{
    $addon = $this->find($id);
    if (!$addon) {
        return 0;
    }
    
    $this->entityManager->beginTransaction();
    
    try {
        // Zvýšit počítadlo stažení v tabulce addons
        $addon->incrementDownloadsCount();
        
        // Přidat záznam do tabulky downloads_log
        $conn = $this->entityManager->getConnection();
        $now = new \DateTime();
        
        $conn->insert('downloads_log', [
            'addon_id' => $id,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
        
        $this->entityManager->flush();
        $this->entityManager->commit();
        
        return 1;
    } catch (\Exception $e) {
        $this->entityManager->rollback();
        throw $e;
    }
}

/**
 * Získá statistiky stažení podle doplňků
 * 
 * @param int $limit Maximální počet doplňků k vrácení
 * @param \DateTime|null $startDate Počáteční datum pro filtrování
 * @return array
 */
public function getDownloadsByAddon(int $limit = 10, ?\DateTime $startDate = null): array
{
    $conn = $this->entityManager->getConnection();
    $qb = $conn->createQueryBuilder();
    
    $qb->select('a.id', 'a.name', 'a.slug', 'COUNT(dl.id) as download_count')
       ->from('downloads_log', 'dl')
       ->join('dl', 'addons', 'a', 'dl.addon_id = a.id');
    
    if ($startDate) {
        $qb->where('dl.created_at >= :startDate')
           ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
    }
    
    $qb->groupBy('a.id', 'a.name', 'a.slug')
       ->orderBy('download_count', 'DESC')
       ->setMaxResults($limit);
    
    $stmt = $qb->executeQuery();
    $data = $stmt->fetchAllAssociative();
    
    return $data;
}

/**
 * Získá statistiky stažení podle denní doby
 * 
 * @param \DateTime|null $startDate Počáteční datum pro filtrování
 * @return array
 */
public function getDownloadsByHourOfDay(?\DateTime $startDate = null): array
{
    $conn = $this->entityManager->getConnection();
    $qb = $conn->createQueryBuilder();
    
    $qb->select('HOUR(dl.created_at) as hour', 'COUNT(*) as download_count')
       ->from('downloads_log', 'dl');
    
    if ($startDate) {
        $qb->where('dl.created_at >= :startDate')
           ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
    }
    
    $qb->groupBy('hour')
       ->orderBy('hour', 'ASC');
    
    $stmt = $qb->executeQuery();
    $data = $stmt->fetchAllAssociative();
    
    // Zajištění, že máme data pro všechny hodiny (0-23)
    $result = array_fill(0, 24, ['hour' => 0, 'download_count' => 0]);
    
    foreach ($data as $row) {
        $hour = (int)$row['hour'];
        $result[$hour] = [
            'hour' => $hour,
            'download_count' => (int)$row['download_count']
        ];
    }
    
    return array_values($result);
}
    
    /**
     * Aktualizuje hodnocení doplňku
     */
    public function updateRating(int $id): void
    {
        $addon = $this->find($id);
        if (!$addon) {
            return;
        }
        
        $qb = $this->entityManager->createQueryBuilder();
        $avgRating = $qb->select('AVG(r.rating)')
            ->from('App\Entity\AddonReview', 'r')
            ->where('r.addon = :addon')
            ->setParameter('addon', $addon)
            ->getQuery()
            ->getSingleScalarResult();
        
        $addon->setRating($avgRating ?: 0);
        $this->entityManager->flush();
    }
    
    // -------------------------------------------------------------------------
    // METODY PRO PRÁCI S RELAČNÍMI DATY
    // -------------------------------------------------------------------------
    
    /**
     * Vytvoří nový doplněk včetně souvisejících entit
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Add screenshots
            foreach ($screenshots as $screenshot) {
                $screenshot->setAddon($addon);
                $addon->addScreenshot($screenshot);
                $this->entityManager->persist($screenshot);
            }
            
            // Add tags
            foreach ($tagIds as $tagId) {
                $tag = $this->entityManager->getReference(Tag::class, $tagId);
                $addon->addTag($tag);
            }
            
            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $addon->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Aktualizuje doplněk včetně souvisejících entit
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Remove existing screenshots
            foreach ($addon->getScreenshots() as $screenshot) {
                $addon->removeScreenshot($screenshot);
                $this->entityManager->remove($screenshot);
            }
            
            // Add new screenshots
            foreach ($screenshots as $screenshot) {
                $screenshot->setAddon($addon);
                $addon->addScreenshot($screenshot);
                $this->entityManager->persist($screenshot);
            }
            
            // Remove existing tags
            foreach ($addon->getTags() as $tag) {
                $addon->removeTag($tag);
            }
            
            // Add new tags
            foreach ($tagIds as $tagId) {
                $tag = $this->entityManager->getReference(Tag::class, $tagId);
                $addon->addTag($tag);
            }
            
            $this->entityManager->persist($addon);
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $addon->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Načte doplněk včetně všech souvisejících entit
     */
    public function getWithRelated(int $id): ?array
    {
        $addon = $this->find($id);
        
        if (!$addon) {
            return null;
        }
        
        // Eager load associated entities
        $author = $addon->getAuthor();
        $category = $addon->getCategory();
        $screenshots = $addon->getScreenshots()->toArray();
        $tags = $addon->getTags()->toArray();
        
        // Get reviews
        $reviews = $this->entityManager->getRepository('App\Entity\AddonReview')
            ->findBy(['addon' => $addon], ['created_at' => 'DESC']);
        
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
 * Získá statistiky doplňků v čase
 * 
 * @param string $interval 'day', 'week', 'month', or 'year'
 * @param int $limit Počet intervalů k vrácení
 * @param string $metric 'downloads', 'ratings', or 'addons'
 * @return array
 */
public function getStatisticsOverTime(string $interval = 'month', int $limit = 12, string $metric = 'downloads'): array
{
    $result = [];
    $now = new \DateTime();
    $currentDate = clone $now;
    
    // Definice formátu data podle intervalu
    switch ($interval) {
        case 'day':
            $dateFormat = 'Y-m-d';
            $dateInterval = 'P1D';
            $dbFormat = '%Y-%m-%d';
            break;
        case 'week':
            $dateFormat = 'Y-W';
            $dateInterval = 'P1W';
            $dbFormat = '%Y-%u';
            break;
        case 'month':
            $dateFormat = 'Y-m';
            $dateInterval = 'P1M';
            $dbFormat = '%Y-%m';
            break;
        case 'year':
            $dateFormat = 'Y';
            $dateInterval = 'P1Y';
            $dbFormat = '%Y';
            break;
        default:
            $dateFormat = 'Y-m';
            $dateInterval = 'P1M';
            $dbFormat = '%Y-%m';
    }
    
    // Nastavení startovního data pro dotaz
    $startDate = clone $now;
    $startDate->sub(new \DateInterval(str_replace('1', (string)($limit+1), $dateInterval)));
    
    // Generování časových period
    $periods = [];
    for ($i = 0; $i < $limit; $i++) {
        $periods[] = $currentDate->format($dateFormat);
        $currentDate->sub(new \DateInterval($dateInterval));
    }
    
    // Seřadit periody chronologicky
    $periods = array_reverse($periods);
    
    // Inicializace struktury výsledku
    foreach ($periods as $period) {
        $result[$period] = [
            'period' => $period,
            'value' => 0
        ];
    }
    
    // Získání dat podle požadované metriky
    $conn = $this->entityManager->getConnection();
    
    if ($metric === 'addons') {
        // Počet nových doplňků v každém období
        $query = $conn->prepare("
            SELECT DATE_FORMAT(a.created_at, :format) AS period, COUNT(a.id) AS count
            FROM addons a
            WHERE a.created_at >= :start_date
            GROUP BY period
            ORDER BY period
        ");
        
        $query->bindValue('format', $dbFormat);
        $query->bindValue('start_date', $startDate->format('Y-m-d H:i:s'));
        $stmt = $query->executeQuery();
        $data = $stmt->fetchAllAssociative();
        
        foreach ($data as $row) {
            if (isset($result[$row['period']])) {
                $result[$row['period']]['value'] = (int)$row['count'];
            }
        }
    } elseif ($metric === 'ratings') {
        // Průměrné hodnocení v každém období
        $query = $conn->prepare("
            SELECT DATE_FORMAT(ar.created_at, :format) AS period, AVG(ar.rating) AS avg_rating
            FROM addon_reviews ar
            WHERE ar.created_at >= :start_date
            GROUP BY period
            ORDER BY period
        ");
        
        $query->bindValue('format', $dbFormat);
        $query->bindValue('start_date', $startDate->format('Y-m-d H:i:s'));
        $stmt = $query->executeQuery();
        $data = $stmt->fetchAllAssociative();
        
        foreach ($data as $row) {
            if (isset($result[$row['period']])) {
                $result[$row['period']]['value'] = round((float)$row['avg_rating'], 2);
            }
        }
    } else {
        // Počet stažení v každém období z tabulky downloads_log
        $query = $conn->prepare("
            SELECT DATE_FORMAT(dl.created_at, :format) AS period, COUNT(*) AS download_count
            FROM downloads_log dl
            WHERE dl.created_at >= :start_date
            GROUP BY period
            ORDER BY period
        ");
        
        $query->bindValue('format', $dbFormat);
        $query->bindValue('start_date', $startDate->format('Y-m-d H:i:s'));
        $stmt = $query->executeQuery();
        $data = $stmt->fetchAllAssociative();
        
        foreach ($data as $row) {
            if (isset($result[$row['period']])) {
                $result[$row['period']]['value'] = (int)$row['download_count'];
            }
        }
    }
    
    return array_values($result);
}
    
    /**
     * Získá distribuci doplňků podle kategorií
     *
     * @return array
     */
    public function getAddonDistributionByCategory(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c.id, c.name, COUNT(a.id) as addon_count')
           ->from(Category::class, 'c')
           ->leftJoin('c.addons', 'a')
           ->groupBy('c.id, c.name')
           ->orderBy('addon_count', 'DESC');
        
        $result = $qb->getQuery()->getResult();
        
        $distribution = [];
        foreach ($result as $row) {
            $distribution[] = [
                'category_id' => $row['id'],
                'category_name' => $row['name'],
                'addon_count' => (int)$row['addon_count']
            ];
        }
        
        return $distribution;
    }
    
    /**
     * Získá distribuci hodnocení
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
        
        $conn = $this->entityManager->getConnection();
        $query = $conn->prepare("
            SELECT rating, COUNT(*) as count
            FROM addon_reviews
            GROUP BY rating
            ORDER BY rating
        ");
        
        $stmt = $query->executeQuery();
        $data = $stmt->fetchAllAssociative();
        
        foreach ($data as $row) {
            $rating = (int)$row['rating'];
            if (isset($distribution[$rating])) {
                $distribution[$rating] = (int)$row['count'];
            }
        }
        
        return $distribution;
    }
    
    /**
     * Získá nejlepší autory podle počtu stažení
     *
     * @param int $limit
     * @return array
     */
    public function getTopAuthorsByDownloads(int $limit = 10): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('au.id, au.name, COUNT(a.id) as addon_count, SUM(a.downloads_count) as total_downloads')
           ->from(Author::class, 'au')
           ->join('au.addons', 'a')
           ->groupBy('au.id, au.name')
           ->orderBy('total_downloads', 'DESC')
           ->setMaxResults($limit);
        
        $result = $qb->getQuery()->getResult();
        
        $authors = [];
        foreach ($result as $row) {
            $authors[] = [
                'author_id' => $row['id'],
                'author_name' => $row['name'],
                'addon_count' => (int)$row['addon_count'],
                'total_downloads' => (int)$row['total_downloads']
            ];
        }
        
        return $authors;
    }
}
