<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AddonReview;
use App\Entity\Addon;
use App\Entity\User;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Cache\DefaultQueryCache;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Repozitář pro práci s recenzemi doplňků
 * 
 * @extends BaseDoctrineRepository<AddonReview>
 */
class AddonReviewRepository extends BaseRepository implements IReviewRepository
{
    private ?CacheItemPoolInterface $cache;

    /**
     * @param EntityManagerInterface $entityManager
     * @param CacheItemPoolInterface|null $cache Volitelná PSR-6 cache
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?CacheItemPoolInterface $cache = null
    ) {
        parent::__construct($entityManager, AddonReview::class);
        $this->cache = $cache;
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Vytvoří novou recenzi doplňku
     * 
     * @param AddonReview $review
     * @return int ID vytvořené recenze
     * @throws \Exception Při chybě při vytváření recenze
     */
    public function create(AddonReview $review): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Kontrola, zda uživatel již nehodnotil tento doplněk
            if ($review->getUser() !== null) {
                $existingReview = $this->findOneBy([
                    'addon' => $review->getAddon(),
                    'user' => $review->getUser()
                ]);
                
                if ($existingReview !== null) {
                    throw new \Exception('Již jste tento doplněk hodnotili.');
                }
            }
            
            $this->entityManager->persist($review);
            $this->entityManager->flush();
            
            // Aktualizace hodnocení doplňku
            $this->updateAddonRating($review->getAddon());
            
            $this->entityManager->commit();
            
            $this->invalidateCache($review->getAddon()->getId());
            
            return $review->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Aktualizuje existující recenzi
     * 
     * @param AddonReview $review
     * @return int ID aktualizované recenze
     * @throws \Exception Při chybě při aktualizaci recenze
     */
    public function update(AddonReview $review): int
    {
        $this->entityManager->beginTransaction();
        
        try {
            $this->entityManager->persist($review);
            $this->entityManager->flush();
            
            // Aktualizace hodnocení doplňku
            $this->updateAddonRating($review->getAddon());
            
            $this->entityManager->commit();
            
            $this->invalidateCache($review->getAddon()->getId());
            
            return $review->getId();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Smaže recenzi doplňku
     * 
     * @param int $id
     * @return int Počet smazaných recenzí
     */
    public function delete(int $id): int
    {
        $review = $this->find($id);
        if (!$review) {
            return 0;
        }
        
        $addonId = $review->getAddon()->getId();
        
        $this->entityManager->beginTransaction();
        
        try {
            $this->entityManager->remove($review);
            $this->entityManager->flush();
            
            // Aktualizace hodnocení doplňku
            $this->updateAddonRating($review->getAddon());
            
            $this->entityManager->commit();
            
            $this->invalidateCache($addonId);
            
            return 1;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * Aktualizuje průměrné hodnocení doplňku na základě recenzí
     * 
     * @param Addon $addon Doplněk, jehož hodnocení se má aktualizovat
     */
    private function updateAddonRating(Addon $addon): void
    {
        try {
            $qb = $this->entityManager->createQueryBuilder();
            $avgRating = $qb->select('AVG(r.rating)')
                ->from(AddonReview::class, 'r')
                ->where('r.addon = :addon')
                ->andWhere('r.is_active = :active')
                ->setParameter('addon', $addon)
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult();
            
            $addon->setRating((float)$avgRating);
            $this->entityManager->flush();
        } catch (NoResultException $e) {
            // Žádné recenze - nastavit hodnocení na 0
            $addon->setRating(0);
            $this->entityManager->flush();
        }
    }
    
    /**
     * Najde recenze pro konkrétní doplněk
     * 
     * @param int $addonId ID doplňku
     * @param bool $activeOnly Vrátit pouze aktivní recenze
     * @return Collection<AddonReview> Kolekce recenzí
     */
    public function findByAddon(int $addonId, bool $activeOnly = true): Collection
    {
        $cacheKey = "reviews_addon_{$addonId}_" . ($activeOnly ? 'active' : 'all');
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }
        
        $qb = $this->createQueryBuilder('r')
            ->where('r.addon = :addon')
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId))
            ->orderBy('r.created_at', 'DESC');
        
        if ($activeOnly) {
            $qb->andWhere('r.is_active = :active')
               ->setParameter('active', true);
        }
        
        $reviews = $qb->getQuery()->getResult();
        $collection = new Collection($reviews);
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set($collection);
            $cacheItem->expiresAfter(3600); // 1 hodina
            $this->cache->save($cacheItem);
        }
        
        return $collection;
    }
    
    /**
     * Najde recenze vytvořené konkrétním uživatelem
     * 
     * @param int $userId ID uživatele
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function findByUser(int $userId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $this->entityManager->getReference(User::class, $userId))
            ->orderBy('r.created_at', 'DESC');
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Vyhledá recenze podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'DESC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('r');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'addon_id':
                    $qb->andWhere('r.addon = :addon')
                       ->setParameter('addon', $this->entityManager->getReference(Addon::class, $value));
                    break;
                
                case 'user_id':
                    $qb->andWhere('r.user = :user')
                       ->setParameter('user', $this->entityManager->getReference(User::class, $value));
                    break;
                
                case 'email':
                    $qb->andWhere('r.email LIKE :email')
                       ->setParameter('email', '%' . $value . '%');
                    break;
                
                case 'name':
                    $qb->andWhere('r.name LIKE :name')
                       ->setParameter('name', '%' . $value . '%');
                    break;
                
                case 'min_rating':
                    $qb->andWhere('r.rating >= :minRating')
                       ->setParameter('minRating', $value);
                    break;
                
                case 'max_rating':
                    $qb->andWhere('r.rating <= :maxRating')
                       ->setParameter('maxRating', $value);
                    break;
                
                case 'has_comment':
                    if ($value) {
                        $qb->andWhere('r.comment IS NOT NULL')
                           ->andWhere('r.comment != :emptyString')
                           ->setParameter('emptyString', '');
                    } else {
                        $qb->andWhere('r.comment IS NULL OR r.comment = :emptyString')
                           ->setParameter('emptyString', '');
                    }
                    break;
                
                case 'is_verified':
                    $qb->andWhere('r.is_verified = :isVerified')
                       ->setParameter('isVerified', (bool)$value);
                    break;
                
                case 'is_active':
                    $qb->andWhere('r.is_active = :isActive')
                       ->setParameter('isActive', (bool)$value);
                    break;
                
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('r.created_at >= :createdAfter')
                           ->setParameter('createdAfter', $value);
                    }
                    break;
                
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('r.created_at <= :createdBefore')
                           ->setParameter('createdBefore', $value);
                    }
                    break;
                
                case 'search':
                    $qb->andWhere('r.comment LIKE :search OR r.name LIKE :search')
                       ->setParameter('search', '%' . $value . '%');
                    break;
                
                default:
                    if (property_exists(AddonReview::class, $key)) {
                        $qb->andWhere("r.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply ordering
        if (property_exists(AddonReview::class, $sortBy)) {
            $qb->orderBy("r.$sortBy", $sortDir);
        } else {
            $qb->orderBy('r.created_at', 'DESC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Poskytuje analýzu sentimentu recenzí pro doplněk
     * 
     * @param int $addonId ID doplňku
     * @param bool $activeOnly Počítat pouze aktivní recenze
     * @return array Výsledek analýzy obsahující počty pozitivních, neutrálních a negativních recenzí
     */
    public function getSentimentAnalysis(int $addonId, bool $activeOnly = true): array
    {
        $cacheKey = "sentiment_addon_{$addonId}_" . ($activeOnly ? 'active' : 'all');
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }
        
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id) as total')
            ->addSelect('SUM(CASE WHEN r.rating >= 4 THEN 1 ELSE 0 END) as positive')
            ->addSelect('SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as neutral')
            ->addSelect('SUM(CASE WHEN r.rating <= 2 THEN 1 ELSE 0 END) as negative')
            ->addSelect('AVG(r.rating) as avgRating')
            ->where('r.addon = :addon')
            ->setParameter('addon', $this->entityManager->getReference(Addon::class, $addonId));
        
        if ($activeOnly) {
            $qb->andWhere('r.is_active = :active')
               ->setParameter('active', true);
        }
        
        $result = $qb->getQuery()->getOneOrNullResult();
        
        if (!$result) {
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'total' => 0,
                'sentiment_score' => 0,
                'avg_rating' => 0
            ];
        }
        
        $total = (int)$result['total'] ?: 1; // Předejít dělení nulou
        $sentimentScore = ((int)$result['positive'] - (int)$result['negative']) / $total;
        
        $analysis = [
            'positive' => (int)$result['positive'],
            'neutral' => (int)$result['neutral'],
            'negative' => (int)$result['negative'],
            'total' => (int)$result['total'],
            'sentiment_score' => round($sentimentScore, 2),
            'avg_rating' => round((float)$result['avgRating'], 2)
        ];
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set($analysis);
            $cacheItem->expiresAfter(3600); // 1 hodina
            $this->cache->save($cacheItem);
        }
        
        return $analysis;
    }
    
    /**
     * Poskytuje časovou řadu aktivity recenzí v určitém intervalu
     * 
     * @param int $addonId ID doplňku
     * @param string $interval Časový interval ('day', 'week', 'month' nebo 'year')
     * @param int $limit Počet období k vrácení
     * @param bool $activeOnly Počítat pouze aktivní recenze
     * @return array Pole s daty pro časovou řadu
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12, bool $activeOnly = true): array
    {
        $cacheKey = "activity_{$interval}_{$addonId}_{$limit}_" . ($activeOnly ? 'active' : 'all');
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }
        
        // Define date format and expression based on interval
        switch ($interval) {
            case 'day':
                $dateFormat = 'Y-m-d';
                $dateExpression = "DATE_FORMAT(r.created_at, '%Y-%m-%d')";
                $dateInterval = 'P1D';
                break;
            case 'week':
                $dateFormat = 'Y-W';
                $dateExpression = "CONCAT(YEAR(r.created_at), '-', WEEK(r.created_at))";
                $dateInterval = 'P1W';
                break;
            case 'month':
                $dateFormat = 'Y-m';
                $dateExpression = "DATE_FORMAT(r.created_at, '%Y-%m')";
                $dateInterval = 'P1M';
                break;
            case 'year':
                $dateFormat = 'Y';
                $dateExpression = "YEAR(r.created_at)";
                $dateInterval = 'P1Y';
                break;
            default:
                $dateFormat = 'Y-m';
                $dateExpression = "DATE_FORMAT(r.created_at, '%Y-%m')";
                $dateInterval = 'P1M';
        }
        
        // Generate periods array
        $now = new \DateTime();
        $periods = [];
        $currentDate = clone $now;
        
        for ($i = 0; $i < $limit; $i++) {
            $periods[$currentDate->format($dateFormat)] = [
                'period' => $currentDate->format($dateFormat),
                'review_count' => 0,
                'average_rating' => 0,
                'ratings' => []
            ];
            
            $currentDate->sub(new \DateInterval($dateInterval));
        }
        
        // Sort periods chronologically
        ksort($periods);
        
        // Get review data from database
        $conn = $this->entityManager->getConnection();
        $sql = "
            SELECT 
                {$dateExpression} AS period,
                COUNT(r.id) AS review_count,
                AVG(r.rating) AS avg_rating
            FROM addon_reviews r
            WHERE r.addon_id = :addonId
        ";
        
        if ($activeOnly) {
            $sql .= " AND r.is_active = :active";
        }
        
        $sql .= "
            GROUP BY period
            ORDER BY period ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('addonId', $addonId);
        
        if ($activeOnly) {
            $stmt->bindValue('active', true);
        }
        
        $result = $stmt->executeQuery();
        
        foreach ($result->fetchAllAssociative() as $row) {
            if (isset($periods[$row['period']])) {
                $periods[$row['period']]['review_count'] = (int)$row['review_count'];
                $periods[$row['period']]['average_rating'] = round((float)$row['avg_rating'], 2);
            }
        }
        
        $timeline = array_values($periods);
        
        if ($this->cache !== null) {
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->set($timeline);
            $cacheItem->expiresAfter(3600); // 1 hodina
            $this->cache->save($cacheItem);
        }
        
        return $timeline;
    }
    
    /**
     * Najde nejčastěji se opakující klíčová slova v komentářích
     * 
     * @param int $addonId ID doplňku
     * @param int $limit Maximální počet klíčových slov
     * @param string $language Jazyk pro stop slova ('cs' nebo 'en')
     * @return array Pole klíčových slov s frekvencemi
     */
    public function findCommonKeywords(int $addonId, int $limit = 10, string $language = 'cs'): array
    {
        // Čeština a angličtina mají rozdílná stop slova
        $stopWords = $this->getStopWords($language);
        
        $reviews = $this->findByAddon($addonId);
        $commentTexts = [];
        
        foreach ($reviews as $review) {
            if ($review->getComment()) {
                $commentTexts[] = $review->getComment();
            }
        }
        
        if (empty($commentTexts)) {
            return [];
        }
        
        // Combine and normalize text
        $combinedText = implode(' ', $commentTexts);
        $combinedText = mb_strtolower($combinedText, 'UTF-8');
        $combinedText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $combinedText);
        
        // Split into words and count
        $words = preg_split('/\s+/', $combinedText);
        $words = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        $wordFrequencies = array_count_values($words);
        arsort($wordFrequencies);
        
        return array_slice($wordFrequencies, 0, $limit, true);
    }
    
    /**
     * Vrátí seznam stop slov pro daný jazyk
     * 
     * @param string $language Kód jazyka
     * @return array Seznam stop slov
     */
    private function getStopWords(string $language): array
    {
        switch ($language) {
            case 'cs':
                return [
                    'a', 'aby', 'ale', 'ani', 'ano', 'asi', 'bez', 'bude', 'budem',
                    'budes', 'by', 'byl', 'byla', 'byli', 'bylo', 'být', 'co', 'což',
                    'cz', 'další', 'dnes', 'do', 'ho', 'i', 'jak', 'jako', 'je', 'jeho',
                    'jej', 'její', 'jen', 'ještě', 'jestli', 'jestliže', 'jí', 'již',
                    'jsem', 'jsi', 'jsou', 'k', 'kde', 'kdo', 'kdy', 'když', 'má', 'mají',
                    'mám', 'máme', 'máš', 'mé', 'mít', 'může', 'na', 'nad', 'napište',
                    'náš', 'naši', 'ne', 'nebo', 'nechť', 'než', 'ní', 'nic', 'nové',
                    'nový', 'o', 'od', 'ode', 'pak', 'po', 'pod', 'podle', 'pokud',
                    'pouze', 'pro', 'proč', 'před', 'přes', 'při', 's', 'se', 'si', 'sice',
                    'strana', 'své', 'svůj', 'svých', 'svým', 'svými', 'ta', 'tak', 'také',
                    'takže', 'tam', 'tato', 'tedy', 'těma', 'ten', 'tento', 'teto', 'tim',
                    'tímto', 'to', 'tohle', 'toho', 'tohoto', 'tom', 'tomto', 'tomuto',
                    'tu', 'tuto', 'ty', 'tyto', 'u', 'už', 'v', 've', 'více', 'všechen',
                    'však', 'vy', 'z', 'za', 'zde', 'že'
                ];
            
            case 'en':
            default:
                return [
                    'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an',
                    'and', 'any', 'are', 'as', 'at', 'be', 'because', 'been', 'before',
                    'being', 'below', 'between', 'both', 'but', 'by', 'can', 'did', 'do',
                    'does', 'doing', 'don', 'down', 'during', 'each', 'few', 'for', 'from',
                    'further', 'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers',
                    'herself', 'him', 'himself', 'his', 'how', 'i', 'if', 'in', 'into', 'is',
                    'it', 'its', 'itself', 'just', 'me', 'more', 'most', 'my', 'myself',
                    'no', 'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or',
                    'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 's', 'same',
                    'she', 'should', 'so', 'some', 'such', 't', 'than', 'that', 'the', 'their',
                    'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this',
                    'those', 'through', 'to', 'too', 'under', 'until', 'up', 'very', 'was',
                    'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom',
                    'why', 'will', 'with', 'you', 'your', 'yours', 'yourself', 'yourselves'
                ];
        }
    }
    
    /**
     * Vrátí recenze s konkrétním hodnocením
     * 
     * @param int $rating Hodnocení (1-5)
     * @param int $page Stránka výsledků
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<AddonReview> Stránkovaná kolekce recenzí
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->findWithFilters(['rating' => $rating], 'created_at', 'DESC', $page, $itemsPerPage);
    }
    
    /**
     * Vrátí nejnovější recenze napříč všemi doplňky
     * 
     * @param int $limit Maximální počet recenzí
     * @return array Pole nejnovějších recenzí s informacemi o doplňcích
     */
    public function getMostRecentReviews(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.addon', 'a')
            ->where('r.is_active = :active')
            ->setParameter('active', true)
            ->orderBy('r.created_at', 'DESC')
            ->setMaxResults($limit);
        
        $reviews = $qb->getQuery()->getResult();
        
        $result = [];
        foreach ($reviews as $review) {
            $addon = $review->getAddon();
            
            $result[] = [
                'review' => $review,
                'addon_name' => $addon->getName(),
                'addon_slug' => $addon->getSlug(),
                'addon_icon_url' => $addon->getIconUrl()
            ];
        }
        
        return $result;
    }
    
    /**
     * Označí recenzi jako ověřenou/verifikovanou
     * 
     * @param int $reviewId ID recenze
     * @param bool $verified Stav ověření
     * @return bool Úspěch operace
     */
    public function setVerified(int $reviewId, bool $verified): bool
    {
        try {
            $review = $this->find($reviewId);
            
            if (!$review) {
                return false;
            }
            
            $review->setIsVerified($verified);
            $this->entityManager->flush();
            
            $this->invalidateCache($review->getAddon()->getId());
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Nastaví aktivitu recenze (zobrazování/skrytí)
     * 
     * @param int $reviewId ID recenze
     * @param bool $active Stav aktivity
     * @return bool Úspěch operace
     */
    public function setActive(int $reviewId, bool $active): bool
    {
        try {
            $review = $this->find($reviewId);
            
            if (!$review) {
                return false;
            }
            
            $review->setIsActive($active);
            $this->entityManager->flush();
            
            // Aktualizuje hodnocení doplňku
            $this->updateAddonRating($review->getAddon());
            
            $this->invalidateCache($review->getAddon()->getId());
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Invaliduje cache pro daný doplněk
     * 
     * @param int $addonId ID doplňku
     */
    private function invalidateCache(int $addonId): void
    {
        if ($this->cache === null) {
            return;
        }
        
        $cacheKeys = [
            "reviews_addon_{$addonId}_active",
            "reviews_addon_{$addonId}_all",
            "sentiment_addon_{$addonId}_active",
            "sentiment_addon_{$addonId}_all"
        ];
        
        foreach ($cacheKeys as $key) {
            $this->cache->deleteItem($key);
        }
        
        // Také zneplatnit aktivitní grafy
        $intervals = ['day', 'week', 'month', 'year'];
        foreach ($intervals as $interval) {
            $this->cache->deleteItem("activity_{$interval}_{$addonId}_12_active");
            $this->cache->deleteItem("activity_{$interval}_{$addonId}_12_all");
        }
    }
    
    /**
     * Získá statistiky recenzí pro dashboard
     *
     * @return array Statistiky recenzí
     */
    public function getDashboardStatistics(): array
    {
        $conn = $this->entityManager->getConnection();
        
        // Celkový počet recenzí
        $totalReviews = $conn->executeQuery("SELECT COUNT(*) FROM addon_reviews")->fetchOne();
        
        // Počet recenzí s komentářem
        $reviewsWithComment = $conn->executeQuery("SELECT COUNT(*) FROM addon_reviews WHERE comment IS NOT NULL AND comment != ''")->fetchOne();
        
        // Průměrné hodnocení
        $avgRating = $conn->executeQuery("SELECT AVG(rating) FROM addon_reviews")->fetchOne();
        
        // Distribuce hodnocení
        $ratings = $conn->executeQuery("
            SELECT rating, COUNT(*) as count 
            FROM addon_reviews 
            GROUP BY rating 
            ORDER BY rating
        ")->fetchAllAssociative();
        
        $ratingDistribution = [];
        foreach ($ratings as $row) {
            $ratingDistribution[$row['rating']] = (int)$row['count'];
        }
        
        // Doplnit chybějící hodnocení
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($ratingDistribution[$i])) {
                $ratingDistribution[$i] = 0;
            }
        }
        
        // Počet recenzí za posledních 7 dnů
        $lastWeekReviews = $conn->executeQuery("
            SELECT COUNT(*) 
            FROM addon_reviews 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ")->fetchOne();
        
        // Počet recenzí za posledních 30 dnů
        $lastMonthReviews = $conn->executeQuery("
            SELECT COUNT(*) 
            FROM addon_reviews 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->fetchOne();
        
        return [
            'total_reviews' => (int)$totalReviews,
            'reviews_with_comment' => (int)$reviewsWithComment,
            'reviews_with_comment_percentage' => $totalReviews > 0 ? round(($reviewsWithComment / $totalReviews) * 100, 1) : 0,
            'average_rating' => round((float)$avgRating, 2),
            'rating_distribution' => $ratingDistribution,
            'last_week_reviews' => (int)$lastWeekReviews,
            'last_month_reviews' => (int)$lastMonthReviews
        ];
    }
}