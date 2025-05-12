<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\AddonReview;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IReviewRepository;
use App\Repository\Interface\IAddonRepository;
use Nette\Database\Explorer;
use Nette\Database\Connection;
use Nette\Database\Structure;
use Nette\Caching\Storage;
use Nette\Database\Conventions\DiscoveredConventions;
/**
 * @extends BaseRepository<AddonReview>
 * @implements ReviewRepositoryInterface
 */
class ReviewRepository extends BaseRepository implements IReviewRepository
{
    /** @var IAddonRepository */
    private IAddonRepository $addonRepository;

    public function __construct(Connection $connection, IAddonRepository $addonRepository, Storage $cacheStorage)
    {
        // Vytvoření Explorer instance z Connection
        $structure = new Structure($connection, $cacheStorage);
        $conventions = new DiscoveredConventions($structure);
        $explorer = new Explorer($connection, $structure, $conventions, $cacheStorage);
        
        parent::__construct($explorer);
        $this->tableName = 'addon_reviews';
        $this->entityClass = AddonReview::class;
        $this->addonRepository = $addonRepository;
    }

    /**
     * Create a new review
     * 
     * @param AddonReview $review
     * @return int
     */
    public function create(AddonReview $review): int
    {
        // Set timestamp
        $review->created_at = new \DateTime();
        
        // Insert the review
        $reviewId = $this->save($review);
        
        // Update addon rating
        $this->addonRepository->updateRating($review->addon_id);
        
        return $reviewId;
    }

    /**
     * Delete a review
     * 
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        // Get the addon ID first
        $review = $this->findById($id);
        $addonId = $review ? $review->addon_id : null;
        
        // Delete the review
        $result = parent::delete($id);
        
        // Update addon rating
        if ($addonId) {
            $this->addonRepository->updateRating($addonId);
        }
        
        return $result;
    }

    /**
     * Find reviews by addon
     * 
     * @param int $addonId
     * @return Collection<AddonReview>
     */
    public function findByAddon(int $addonId): Collection
    {
        $rows = $this->findBy(['addon_id' => $addonId])
            ->order('created_at DESC');
        
        $reviews = [];
        foreach ($rows as $row) {
            $reviews[] = AddonReview::fromArray($row->toArray());
        }
        
        return new Collection($reviews);
    }

    /**
     * Find reviews with advanced filtering
     * 
     * @param array $filters Filtering criteria
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC or DESC)
     * @param int $page Page number
     * @param int $itemsPerPage Items per page
     * @return PaginatedCollection<AddonReview>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'created_at', string $sortDir = 'DESC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $selection = $this->getTable();
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'addon_id':
                    $selection->where('addon_id', $value);
                    break;
                    
                case 'user_id':
                    $selection->where('user_id', $value);
                    break;
                    
                case 'email':
                    $selection->where("email LIKE ?", "%{$value}%");
                    break;
                    
                case 'name':
                    $selection->where("name LIKE ?", "%{$value}%");
                    break;
                    
                case 'min_rating':
                    $selection->where('rating >= ?', $value);
                    break;
                    
                case 'max_rating':
                    $selection->where('rating <= ?', $value);
                    break;
                    
                case 'has_comment':
                    if ($value) {
                        $selection->where('comment IS NOT NULL AND comment != ?', '');
                    } else {
                        $selection->where('comment IS NULL OR comment = ?', '');
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
                    if (property_exists('App\Model\AddonReview', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Count total matching records
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Apply sorting
        if (property_exists('App\Model\AddonReview', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order("created_at DESC"); // Default sorting
        }
        
        // Apply pagination
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Convert to entities
        $items = [];
        foreach ($selection as $row) {
            $items[] = AddonReview::fromArray($row->toArray());
        }
        
        // Create collection and paginated collection
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
     * Get sentiment analysis of reviews
     * 
     * @param int $addonId
     * @return array
     */
    public function getSentimentAnalysis(int $addonId): array
    {
        $reviews = $this->findByAddon($addonId);
        
        if ($reviews->count() === 0) {
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
            if ($review->rating >= 4) {
                $positive++;
            } elseif ($review->rating <= 2) {
                $negative++;
            } else {
                $neutral++;
            }
        }
        
        $total = $reviews->count();
        $sentimentScore = ($positive - $negative) / $total;
        
        return [
            'positive' => $positive,
            'neutral' => $neutral,
            'negative' => $negative,
            'sentiment_score' => round($sentimentScore, 2)
        ];
    }

    /**
     * Get review activity over time
     * 
     * @param int $addonId
     * @param string $interval 'day', 'week', 'month', or 'year'
     * @param int $limit Number of periods to return
     * @return array
     */
    public function getReviewActivityOverTime(int $addonId, string $interval = 'month', int $limit = 12): array
    {
        $now = new \DateTime();
        $currentDate = clone $now;
        
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
        for ($i = 0; $i < $limit; $i++) {
            $periods[] = $currentDate->format($dateFormat);
            $currentDate->sub(new \DateInterval($dateInterval));
        }
        
        // Sort periods chronologically
        $periods = array_reverse($periods);
        
        // Initialize result structure
        $result = [];
        foreach ($periods as $period) {
            $result[$period] = [
                'period' => $period,
                'review_count' => 0,
                'average_rating' => 0
            ];
        }
        
        // Query the database for review counts and average ratings
        $startDate = clone $currentDate;
        
        $query = $this->database->query("
            SELECT DATE_FORMAT(created_at, '$sqlFormat') AS period, 
                   COUNT(*) AS review_count,
                   AVG(rating) AS avg_rating
            FROM {$this->tableName}
            WHERE addon_id = ? AND created_at >= ?
            GROUP BY period
            ORDER BY period
        ", $addonId, $startDate->format('Y-m-d H:i:s'));
        
        foreach ($query as $row) {
            if (isset($result[$row->period])) {
                $result[$row->period]['review_count'] = (int)$row->review_count;
                $result[$row->period]['average_rating'] = round((float)$row->avg_rating, 2);
            }
        }
        
        return array_values($result);
    }

    /**
     * Get most recent reviews
     * 
     * @param int $limit
     * @return array
     */
    public function getMostRecentReviews(int $limit = 10): array
    {
        $rows = $this->findAll()
            ->order('created_at DESC')
            ->limit($limit);
            
        $reviews = [];
        foreach ($rows as $row) {
            $review = AddonReview::fromArray($row->toArray());
            
            // Get addon name
            $addon = $this->database->table('addons')
                ->get($review->addon_id);
                
            $reviews[] = [
                'review' => $review,
                'addon_name' => $addon ? $addon->name : 'Unknown Addon',
                'addon_slug' => $addon ? $addon->slug : null
            ];
        }
        
        return $reviews;
    }

    /**
     * Get reviews by rating
     * 
     * @param int $rating
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<AddonReview>
     */
    public function getReviewsByRating(int $rating, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->findWithFilters(['rating' => $rating], 'created_at', 'DESC', $page, $itemsPerPage);
    }

    /**
     * Find common keywords in reviews (basic text analysis)
     * 
     * @param int $addonId
     * @param int $limit
     * @return array
     */
    public function findCommonKeywords(int $addonId, int $limit = 10): array
    {
        $reviews = $this->findByAddon($addonId);
        
        $commentTexts = [];
        foreach ($reviews as $review) {
            if (!empty($review->comment)) {
                $commentTexts[] = $review->comment;
            }
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