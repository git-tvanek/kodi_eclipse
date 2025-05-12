<?php

declare(strict_types=1);

namespace App\Presentation\Review;

use App\Presentation\BasePresenter;
use App\Facade\ReviewFacade;
use App\Facade\AddonFacade;
use App\Forms\Factory\ReviewFormFactory;
use Nette\Application\UI\Form;

class ReviewPresenter extends BasePresenter
{
    /** @var ReviewFacade */
    private ReviewFacade $reviewFacade;
    
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var ReviewFormFactory */
    private ReviewFormFactory $reviewFormFactory;
    
    /** @var int|null */
    private ?int $userId = null;
    
    /** @var bool */
    protected bool $userLoggedIn = false;
    
    /**
     * Constructor
     */
    public function __construct(
        ReviewFacade $reviewFacade,
        AddonFacade $addonFacade,
        ReviewFormFactory $reviewFormFactory
    ) {
        $this->reviewFacade = $reviewFacade;
        $this->addonFacade = $addonFacade;
        $this->reviewFormFactory = $reviewFormFactory;
        
        // In a real application, you would get these from a user service/authentication
        $this->userId = null;
        $this->userLoggedIn = false;
    }
    
    /**
     * Default action - list all reviews
     */
    public function renderDefault(int $page = 1): void
    {
        $itemsPerPage = 20;
        
        // Get all reviews with pagination
        $reviews = $this->reviewFacade->findReviews([], $page, $itemsPerPage);
        
        // Get recent reviews
        $recentReviews = $this->reviewFacade->getRecentReviews(5);
        
        $this->template->reviews = $reviews;
        $this->template->recentReviews = $recentReviews;
        $this->template->page = $page;
    }
    
    /**
     * Add action - form for adding a new review
     */
    public function renderAdd(int $addonId): void
    {
        // Get addon
        $addon = $this->addonFacade->getAddonDetail($addonId);
        if (!$addon) {
            $this->error('Addon not found');
        }
        
        $this->template->addon = $addon['addon'];
        $this->template->title = 'Review: ' . $addon['addon']->name;
    }
    
    /**
     * Stats action - show review statistics for an addon
     */
    public function renderStats(int $addonId): void
    {
        // Get addon
        $addon = $this->addonFacade->getAddonDetail($addonId);
        if (!$addon) {
            $this->error('Addon not found');
        }
        
        // Get review sentiment analysis
        $sentimentAnalysis = $this->reviewFacade->getReviewSentiment($addonId);
        
        // Get review activity over time
        $reviewActivity = $this->reviewFacade->getReviewActivity($addonId, 'month', 12);
        
        // Get common keywords in reviews
        $reviewKeywords = $this->reviewFacade->getReviewKeywords($addonId, 20);
        
        $this->template->addon = $addon['addon'];
        $this->template->sentiment = $sentimentAnalysis;
        $this->template->activity = $reviewActivity;
        $this->template->keywords = $reviewKeywords;
    }
    
    /**
     * Component factory for the review form
     */
    protected function createComponentReviewForm(): Form
    {
        $addonId = (int) $this->getParameter('addonId');
        
        $form = $this->reviewFormFactory->create($this->userLoggedIn, $this->userId);
        
        // Set callback for form processing
        $form->onSuccess[] = function (array $data) {
            $this->processReviewForm($data);
        };
        
        return $form->create($addonId);
    }
    
    /**
     * Process review form
     */
    private function processReviewForm(array $data): void
    {
        if ($this->userLoggedIn && $this->userId) {
            // Create review from logged-in user
            $this->reviewFacade->createUserReview(
                $data['addon_id'],
                $this->userId,
                $data['rating'],
                $data['comment'] ?? null
            );
        } else {
            // Create review from guest
            $this->reviewFacade->createGuestReview(
                $data['addon_id'],
                $data['name'],
                $data['email'] ?? null,
                $data['rating'],
                $data['comment'] ?? null
            );
        }
        
        // Get addon detail
        $addon = $this->addonFacade->getAddonDetail($data['addon_id']);
        
        $this->flashMessage('Review added successfully', 'success');
        $this->redirect('Addon:detail', $addon['addon']->slug);
    }
    
    /**
     * Delete action - remove a review
     */
    public function actionDelete(int $id): void
    {
        // Check if user is logged in (in a real app)
        // if (!$this->getUser()->isLoggedIn() || !$this->getUser()->isInRole('admin')) {
        //     $this->flashMessage('You must be an administrator to delete reviews', 'danger');
        //     $this->redirect('Review:default');
        // }
        
        // Delete the review
        $result = $this->reviewFacade->deleteReview($id);
        
        if ($result) {
            $this->flashMessage('Review deleted successfully', 'success');
        } else {
            $this->flashMessage('Failed to delete review', 'danger');
        }
        
        $this->redirect('Review:default');
    }
}