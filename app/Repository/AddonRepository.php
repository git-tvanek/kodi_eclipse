<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Screenshot;
use App\Collection\Collection;
use App\Collection\AddonCollection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IAddonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Tools\Pagination\PaginatorInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Orx;


/**
 * Repozitář pro práci s doplňky
 * 
 * @extends BaseDoctrineRepository<Addon>
 */
class AddonRepository extends BaseRepository implements IAddonRepository
{
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Addon::class);
    }
    
    /**
     * Vytvoří typovanou kolekci doplňků
     * 
     * @param array<Addon> $entities Pole entit
     * @return Collection<Addon> Typovaná kolekce
     */
    /**protected function createCollection(array $entities): Collection
    {
        return new AddonCollection($entities);
    }*/

    /**
    * Vytvoří typovanou kolekci doplňků
    * 
    * @param array<Addon> $entities Pole entit
    * @return Collection<Addon> Typovaná kolekce
    */
    protected function createCollection(array $entities): Collection
    {
    return new Collection($entities); // Namísto AddonCollection použijeme základní Collection
    }
    
    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->findOneBy(['slug' => $slug]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.author = :author')
            ->setParameter('author', $this->entityManager->getReference(Author::class, $authorId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.category = :category')
            ->setParameter('category', $this->entityManager->getReference(Category::class, $categoryId))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findByCategoryRecursive(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        // Získat všechny podkategorie
        $categoryIds = [$categoryId];
        $this->findAllSubcategoryIds($categoryId, $categoryIds);
        
        // Vyhledat doplňky ve všech kategoriích
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.category IN (:categories)')
            ->setParameter('categories', array_map(
                fn($id) => $this->entityManager->getReference(Category::class, $id),
                $categoryIds
            ))
            ->orderBy('a.name', 'ASC');

        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Pomocná metoda pro rekurzivní hledání ID všech podkategorií
     * 
     * @param int $parentId ID rodičovské kategorie
     * @param array &$categoryIds Reference na pole, kam se ukládají ID kategorií
     */
    private function findAllSubcategoryIds(int $parentId, array &$categoryIds): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $subcategories = $qb->select('c.id')
            ->from(Category::class, 'c')
            ->where('c.parent = :parent')
            ->setParameter('parent', $this->entityManager->getReference(Category::class, $parentId))
            ->getQuery()
            ->getArrayResult();
        
        foreach ($subcategories as $subcategory) {
            $subId = $subcategory['id'];
            $categoryIds[] = $subId;
            $this->findAllSubcategoryIds($subId, $categoryIds);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function findPopular(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.downloads_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->createCollection($addons);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findTopRated(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->where('a.rating > 0') // Jen doplňky s hodnocením
            ->orderBy('a.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->createCollection($addons);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findNewest(int $limit = 10): Collection
    {
        $addons = $this->createQueryBuilder('a')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->createCollection($addons);
    }
    
    /**
     * {@inheritDoc}
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        $addon = $this->find($addonId);
        if (!$addon) {
            return $this->createCollection([]);
        }
        
        $category = $addon->getCategory();
        $tags = $addon->getTags();
        
        $qb = $this->createQueryBuilder('a')
            ->where('a.id != :addonId')
            ->setParameter('addonId', $addonId);
        
        // Pokud má doplněk tagy, použít je pro hledání podobných
        if (count($tags) > 0) {
            $qb->join('a.tags', 't')
               ->andWhere('t IN (:tags)')
               ->andWhere('a.category = :category')
               ->setParameter('tags', $tags)
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->groupBy('a.id')
               ->setMaxResults($limit);
               
            // Optimalizace - přidání počtu shodných tagů
            $qb->addSelect('COUNT(t.id) AS HIDDEN tagCount')
               ->having('tagCount > 0')
               ->orderBy('tagCount', 'DESC')
               ->addOrderBy('a.downloads_count', 'DESC');
        } else {
            // Jinak jen použít kategorii
            $qb->andWhere('a.category = :category')
               ->setParameter('category', $category)
               ->orderBy('a.downloads_count', 'DESC')
               ->setMaxResults($limit);
        }
        
        $similarAddons = $qb->getQuery()->getResult();
        
        return $this->createCollection($similarAddons);
    }
    
    /**
     * {@inheritDoc}
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        if (empty(trim($query))) {
            // Prázdný dotaz - vrátíme prázdný výsledek
            return new PaginatedCollection(
                $this->createCollection([]),
                0,
                $page,
                $itemsPerPage,
                0
            );
        }
        
        $qb = $this->createQueryBuilder('a');
        
        // Rozdělit dotaz na klíčová slova
        $keywords = preg_split('/\s+/', trim($query));
        
        // Vytvořit OR podmínky pro hledání v názvu a popisu
        $orExpressions = $qb->expr()->orX();
        
        foreach ($keywords as $keyword) {
            $orExpressions->add($qb->expr()->like('a.name', ':keyword_' . md5($keyword)));
            $orExpressions->add($qb->expr()->like('a.description', ':keyword_' . md5($keyword)));
            $qb->setParameter('keyword_' . md5($keyword), '%' . $keyword . '%');
        }
        
        $qb->andWhere($orExpressions)
           ->orderBy('a.downloads_count', 'DESC');
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * {@inheritDoc}
     */
    public function advancedSearch(string $query, array $fields = ['name', 'description'], array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('a');
        
        // Aplikovat filtry
        $qb = $this->applyFilters($qb, $filters);
        
        // Pokud je zadaný vyhledávací dotaz, aplikovat jej
        if (!empty(trim($query))) {
            $keywords = preg_split('/\s+/', trim($query));
            $orExpressions = $qb->expr()->orX();
            
            foreach ($keywords as $keyword) {
                foreach ($fields as $field) {
                    if (property_exists(Addon::class, $field)) {
                        $orExpressions->add($qb->expr()->like('a.' . $field, ':keyword_' . md5($keyword . $field)));
                        $qb->setParameter('keyword_' . md5($keyword . $field), '%' . $keyword . '%');
                    }
                }
            }
            
            if ($orExpressions->count() > 0) {
                $qb->andWhere($orExpressions);
            }
        }
        
        // Řazení: pokud je zadaný dotaz, řadíme podle relevance (stažení)
        // jinak podle zadaného řazení ve filtrech nebo výchozí podle názvu
        if (!empty($query)) {
            $qb->orderBy('a.downloads_count', 'DESC');
        } elseif (isset($filters['sort_by']) && isset($filters['sort_dir'])) {
            $qb->orderBy('a.' . $filters['sort_by'], $filters['sort_dir']);
        } else {
            $qb->orderBy('a.name', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * {@inheritDoc}
     */
    public function incrementDownloadCount(int $id, ?string $ipAddress = null, ?string $userAgent = null): int
    {
        $addon = $this->find($id);
        if (!$addon) {
            return 0;
        }
        
        $this->entityManager->beginTransaction();
        
        try {
            // Zvýšit počítadlo stažení
            $addon->incrementDownloadsCount();
            
            // Přidat záznam do tabulky downloads_log (pokud existuje)
            if ($ipAddress !== null) {
                $conn = $this->entityManager->getConnection();
                $now = new \DateTime();
                
                $conn->insert('downloads_log', [
                    'addon_id' => $id,
                    'created_at' => $now->format('Y-m-d H:i:s'),
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent
                ]);
            }
            
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return 1;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function updateRating(int $id): void
    {
        $addon = $this->find($id);
        if (!$addon) {
            return;
        }
        
        $qb = $this->entityManager->createQueryBuilder();
        $result = $qb->select('COUNT(r.id) as reviewCount, AVG(r.rating) as avgRating')
            ->from('App\Entity\AddonReview', 'r')
            ->where('r.addon = :addon')
            ->setParameter('addon', $addon)
            ->getQuery()
            ->getSingleResult();
        
        $reviewCount = $result['reviewCount'] ?? 0;
        $avgRating = $result['avgRating'] ?? 0;
        
        if ($reviewCount > 0) {
            $addon->setRating((float)$avgRating);
            $this->entityManager->flush();
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function createWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        $this->validateAddon($addon);
        
        $this->entityManager->beginTransaction();
        
        try {
            // Nastavit časová razítka
            if (empty($addon->getCreatedAt())) {
                $addon->setCreatedAt(new \DateTime());
            }
            if (empty($addon->getUpdatedAt())) {
                $addon->setUpdatedAt(new \DateTime());
            }
            
            // Uložit doplněk
            $this->entityManager->persist($addon);
            
            // Přidat screenshoty
            foreach ($screenshots as $screenshot) {
                if ($screenshot instanceof Screenshot) {
                    $screenshot->setAddon($addon);
                    $addon->addScreenshot($screenshot);
                    $this->entityManager->persist($screenshot);
                }
            }
            
            // Přidat tagy
            foreach ($tagIds as $tagId) {
                $tag = $this->entityManager->getReference(Tag::class, $tagId);
                $addon->addTag($tag);
            }
            
            $this->entityManager->flush();
            $this->entityManager->commit();
            
            return $addon->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function updateWithRelated(Addon $addon, array $screenshots = [], array $tagIds = []): int
    {
        // Kontrola existence
        if (!$this->exists($addon->getId())) {
            throw new \Exception("Doplněk s ID {$addon->getId()} neexistuje.");
        }
        
        $this->validateAddon($addon);
        
        $this->entityManager->beginTransaction();
        
        try {
            // Aktualizovat časové razítko
            $addon->setUpdatedAt(new \DateTime());
            
            // Odstranit existující screenshoty
            foreach ($addon->getScreenshots() as $screenshot) {
                $addon->removeScreenshot($screenshot);
                $this->entityManager->remove($screenshot);
            }
            
            // Přidat nové screenshoty
            foreach ($screenshots as $screenshot) {
                if ($screenshot instanceof Screenshot) {
                    $screenshot->setAddon($addon);
                    $addon->addScreenshot($screenshot);
                    $this->entityManager->persist($screenshot);
                }
            }
            
            // Odstranit existující tagy
            foreach ($addon->getTags() as $tag) {
                $addon->removeTag($tag);
            }
            
            // Přidat nové tagy
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
     * {@inheritDoc}
     */
    public function getWithRelated(int $id): ?array
    {
        $addon = $this->find($id);
        
        if (!$addon) {
            return null;
        }
        
        // Eager načtení všech souvisejících entit
        $author = $addon->getAuthor();
        $category = $addon->getCategory();
        $screenshots = $addon->getScreenshots()->toArray();
        $tags = $addon->getTags()->toArray();
        
        // Získání recenzí
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
     * {@inheritDoc}
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
        $result = $stmt->fetchAllAssociative();
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
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
            if ($hour >= 0 && $hour < 24) {
                $result[$hour] = [
                    'hour' => $hour,
                    'download_count' => (int)$row['download_count']
                ];
            }
        }
        
        return array_values($result);
    }
    
    /**
     * {@inheritDoc}
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
            // Počet stažení v každém období
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
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
    
    /**
     * {@inheritDoc}
     */
    public function createFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        return $this->applyFilters($qb, $filters);
    }
    
    /**
     * Aplikuje filtry na QueryBuilder
     * 
     * @param QueryBuilder $qb QueryBuilder pro doplňky
     * @param array $filters Pole filtrů
     * @return QueryBuilder Upravený QueryBuilder
     */
    protected function applyFilters(QueryBuilder $qb, array $filters): QueryBuilder
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
                    } else if (!is_array($value) && $value) {
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
                    } else if (!is_array($value) && $value) {
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
                    } else if (!is_array($value) && $value) {
                        $tag = $this->entityManager->getReference(Tag::class, $value);
                        $qb->join('a.tags', 'tag')
                           ->andWhere('tag = :tag')
                           ->setParameter('tag', $tag);
                    }
                    break;
                
                case 'min_rating':
                    $qb->andWhere('a.rating >= :minRating')
                       ->setParameter('minRating', (float)$value);
                    break;
                
                case 'max_rating':
                    $qb->andWhere('a.rating <= :maxRating')
                       ->setParameter('maxRating', (float)$value);
                    break;
                
                case 'min_downloads':
                    $qb->andWhere('a.downloads_count >= :minDownloads')
                       ->setParameter('minDownloads', (int)$value);
                    break;
                
                case 'max_downloads':
                    $qb->andWhere('a.downloads_count <= :maxDownloads')
                       ->setParameter('maxDownloads', (int)$value);
                    break;
                
                case 'kodi_version':
                    $qb->andWhere('(a.kodi_version_min IS NULL OR a.kodi_version_min <= :kodiVersion)')
                       ->andWhere('(a.kodi_version_max IS NULL OR a.kodi_version_max >= :kodiVersion)')
                       ->setParameter('kodiVersion', $value);
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
                    // Pro standardní pole entity
                    $reflectionClass = new \ReflectionClass(Addon::class);
                    if ($reflectionClass->hasProperty($key)) {
                        $qb->andWhere("a.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        return $qb;
    }
    
    /**
     * Validuje doplněk před uložením/aktualizací
     * 
     * @param Addon $addon Entity doplňku
     * @throws \InvalidArgumentException Pokud je doplněk nevalidní
     */
    private function validateAddon(Addon $addon): void
    {
        // Kontrola verzí
        if (!empty($addon->getKodiVersionMin()) && !empty($addon->getKodiVersionMax())) {
            if (version_compare($addon->getKodiVersionMin(), $addon->getKodiVersionMax(), '>')) {
                throw new \InvalidArgumentException('Minimální verze Kodi nemůže být větší než maximální verze');
            }
        }
        
        // Kontrola povinných polí
        if (empty($addon->getName())) {
            throw new \InvalidArgumentException('Název doplňku je povinný');
        }
        
        if (empty($addon->getVersion())) {
            throw new \InvalidArgumentException('Verze doplňku je povinná');
        }
        
        if (empty($addon->getDownloadUrl())) {
            throw new \InvalidArgumentException('URL pro stažení je povinné');
        }
        
        // Kontrola slugu, případně vytvoření z názvu
        if (empty($addon->getSlug())) {
        // Použití factory pro vytvoření inflektoru
        $inflector = \Doctrine\Inflector\InflectorFactory::create()->build();
        $slug = $inflector->urlize($addon->getName());
        $addon->setSlug($slug);
}
        }
    }
