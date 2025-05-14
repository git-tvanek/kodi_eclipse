<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\AddonReview;
use App\Entity\Addon;
use App\Entity\User;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends BaseDoctrineRepository<AddonReview>
 */
class AddonReviewRepository extends BaseDoctrineRepository implements IReviewRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, AddonReview::class);
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function create(AddonReview $review): int
    {
        // Set timestamp if not already set
        if (!$review->getCreatedAt()) {
            $review->setCreatedAt(new \DateTime());
        }
        
        $this->entityManager->persist($review);
        $this->entityManager->flush();
        
        // Update addon rating
        $this->updateAddonRating($review->getAddon());
        
        return $review->getId();
    }
    
    public function delete(int $id): int
    {
        $review = $this->find($id);
        
        if (!$review) {
            return 0;
        }
        
        $addon = $review->getAddon();
        
        $this->entityManager->remove($review);
        $this->entityManager->flush();
        
        // Update addon rating
        $this->updateAddonRating($addon);
        
        return 1;
    }
    
    private function updateAddonRating(Addon $addon): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $avgRating = $qb->select('AVG(r.rating)')
            ->from(AddonReview::class, 'r')
            ->where('r.addon = :addon')
            ->setParameter('addon', $addon)
            ->getQuery()
            ->getSingleScalarResult();
        
        $addon->setRating($avgRating ?: 0);
        $this->entityManager->flush();
    }
    
    public function findByAddon(int $addonId): Collection
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $reviews = $this->findBy(['addon' => $addon], ['created_at' => 'DESC']);
        
        return new Collection($reviews);
    }
    
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
                
                default:
                    if (property_exists(AddonReview::class, $key)) {
                        $qb->andWhere("r.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply sorting
        if (property_exists(AddonReview::class, $sortBy)) {
            $qb->orderBy("r.$sortBy", $sortDir);
        } else {
            $qb->orderBy('r.created_at', 'DESC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function getSentimentAnalysis(int $addonId): array
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $reviews = $this->findBy(['addon' => $addon]);
        
        if (empty($reviews)) {
            return [
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'sentiment_score' => 0
            ];
        }
        
        $positive = 0;
        $neutral = 0;
        $negative = 0;
        
        foreach ($reviews as $review) {
            if ($review->getRating() >= 4) {
                $positive++;
            } elseif ($review->getRating() <= 2) {
                $negative++;
            } else {
                $neutral++;
            }
        }
        
        $total = count($reviews);
        $sentimentScore = ($positive - $negative) / $total;
        
        return [
            'positive' => $positive,
            'neutral' => $neutral,
            'negative' => $negative,
            'sentiment_score' => round($sentimentScore, 2)
        ];
    }
    
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        
        // Define date format based on interval
        $dateFormat = 'Y-m';
        $dateInterval = 'P1M';
        
        switch ($interval) {
            case 'day':
                $dateFormat = 'Y-m-d';
                $dateInterval = 'P1D';
                break;
            case 'week':
                $dateFormat = 'Y-W';
                $dateInterval = 'P1W';
                break;
            case 'month':
                $dateFormat = 'Y-m';
                $dateInterval = 'P1M';
                break;
            case 'year':
                $dateFormat = 'Y';
                $dateInterval = 'P1Y';
                break;
        }
        
        // Generate time periods
        $now = new \DateTime();
        $currentDate = clone $now;
        $periods = [];
        
        for ($i = 0; $i < $limit; $i++) {
            $period = $currentDate->format($dateFormat);
            $periods[$period] = [
                'period' => $period,
                'review_count' => 0,
                'average_rating' => 0,
                'ratings' => []
            ];
            
            $currentDate->sub(new \DateInterval($dateInterval));
        }
        
        // Sort periods chronologically
        ksort($periods);
        
        // Find all reviews for this addon
        $reviews = $this->findBy(['addon' => $addon], ['created_at' => 'DESC']);
        
        // Group reviews by period
        foreach ($reviews as $review) {
            $period = $review->getCreatedAt()->format($dateFormat);
            
            if (isset($periods[$period])) {
                $periods[$period]['review_count']++;
                $periods[$period]['ratings'][] = $review->getRating();
            }
        }
        
        // Calculate average ratings
        foreach ($periods as &$period) {
            if (!empty($period['ratings'])) {
                $period['average_rating'] = round(array_sum($period['ratings']) / count($period['ratings']), 2);
            }
            unset($period['ratings']);
        }
        
        return array_values($periods);
    }
    
    public function getMostRecentReviews(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.addon', 'a')
            ->orderBy('r.created_at', 'DESC')
            ->setMaxResults($limit);
        
        $reviews = $qb->getQuery()->getResult();
        
        $result = [];
        foreach ($reviews as $review) {
            $addon = $review->getAddon();
            
            $result[] = [
                'review' => $review,
                'addon_name' => $addon->getName(),
                'addon_slug' => $addon->getSlug()
            ];
        }
        
        return $result;
    }
    
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.rating = :rating')
            ->setParameter('rating', $rating)
            ->orderBy('r.created_at', 'DESC');
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function findCommonKeywords(int $addonId, int $limit = 10): array
    {
        $addon = $this->entityManager->getReference(Addon::class, $addonId);
        $reviews = $this->findBy(['addon' => $addon]);
        
        // Extract comments
        $commentTexts = [];
        foreach ($reviews as $review) {
            if ($review->getComment()) {
                $commentTexts[] = $review->getComment();
            }
        }
        
        // If no comments, return empty array
        if (empty($commentTexts)) {
            return [];
        }
        
        // Combine all comments
        $combinedText = implode(' ', $commentTexts);
        
        // Convert to lowercase
        $combinedText = strtolower($combinedText);
        
        // Remove punctuation
        $combinedText = preg_replace('/[^\p{L}\p{N}\s]/u', '', $combinedText);
        
        // Split into words
        $words = preg_split('/\s+/', $combinedText);
        
        // Count word frequencies
        $wordFrequencies = array_count_values($words);
        
        // Filter out common stop words
        $stopWords = ['the', 'and', 'a', 'to', 'of', 'is', 'in', 'it', 'that', 'this', 'for', 'with', 'on', 'i', 'you', 'are', 'as', 'be', 'by', 'was', 'has', 'have', 'had'];
        foreach ($stopWords as $stopWord) {
            unset($wordFrequencies[$stopWord]);
        }
        
        // Sort by frequency
        arsort($wordFrequencies);
        
        // Return the most common words
        return array_slice($wordFrequencies, 0, $limit, true);
    }
}
        
        foreach ($categories as $category) {
            $categoriesById[$category->getId()] = $category;
            $hierarchy[$category->getId()] = [
                'category' => $category,
                'children' => [],
                'addon_count' => 0,
                'total_downloads' => 0
            ];
        }
        
        // Build hierarchy
        foreach ($categories as $category) {
            if ($category->getParent()) {
                $parentId = $category->getParent()->getId();
                
                if (isset($categoriesById[$parentId])) {
                    $hierarchy[$parentId]['children'][] = &$hierarchy[$category->getId()];
                    
                    // Update stats
                    $hierarchy[$parentId]['addon_count'] += count($category->getAddons());
                    $hierarchy[$parentId]['total_downloads'] += array_sum(array_map(function($addon) {
                        return $addon->getDownloadsCount();
                    }, $category->getAddons()->toArray()));
                } else {
                    unset($hierarchy[$category->getId()]);
                }
            } else {
                // Update stats for root categories
                $hierarchy[$category->getId()]['addon_count'] += count($category->getAddons());
                $hierarchy[$category->getId()]['total_downloads'] += array_sum(array_map(function($addon) {
                    return $addon->getDownloadsCount();
                }, $category->getAddons()->toArray()));
            }
        }