<?php

declare(strict_types=1);

namespace App\Presentation\Search;

use App\Presentation\BasePresenter;
use App\Facade\SearchFacade;
use App\Facade\CategoryFacade;
use App\Facade\TagFacade;
use App\Forms\Factory\SearchFormFactory;
use Nette\Application\UI\Form;

class SearchPresenter extends BasePresenter
{
    /** @var SearchFacade */
    private SearchFacade $searchFacade;
    
    /** @var CategoryFacade */
    private CategoryFacade $categoryFacade;
    
    /** @var TagFacade */
    private TagFacade $tagFacade;
    
    /** @var SearchFormFactory */
    private SearchFormFactory $searchFormFactory;
    
    /**
     * Constructor
     */
    public function __construct(
        SearchFacade $searchFacade,
        CategoryFacade $categoryFacade,
        TagFacade $tagFacade,
        SearchFormFactory $searchFormFactory
    ) {
        $this->searchFacade = $searchFacade;
        $this->categoryFacade = $categoryFacade;
        $this->tagFacade = $tagFacade;
        $this->searchFormFactory = $searchFormFactory;
    }
    
    /**
     * Default action - search with simple query
     */
    public function renderDefault(string $query = '', int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get search results
        $searchResults = $this->searchFacade->search($query, $page, $itemsPerPage);
        
        $this->template->query = $query;
        $this->template->results = $searchResults;
        $this->template->page = $page;
    }
    
    /**
     * Advanced search action - search with filters
     */
    public function renderAdvanced(int $page = 1): void
    {
        $itemsPerPage = 12;
        
        // Get filter values from request
        $filters = $this->getFiltersFromRequest();
        $query = $this->getParameter('query', '');
        
        // Get advanced search results
        $searchResults = [];
        if (!empty($query) || !empty($filters)) {
            $searchResults = $this->searchFacade->advancedSearch($query, $filters, $page, $itemsPerPage);
        }
        
        // Get root categories for filter form
        $categories = $this->categoryFacade->getRootCategories();
        
        // Get popular tags for filter form
        $popularTags = $this->tagFacade->getTagsWithCounts();
        
        $this->template->query = $query;
        $this->template->filters = $filters;
        $this->template->results = $searchResults;
        $this->template->categories = $categories;
        $this->template->popularTags = $popularTags;
        $this->template->page = $page;
    }
    
    /**
     * Component factory for the search form
     */
    protected function createComponentSearchForm(): Form
    {
        $searchForm = $this->searchFormFactory->create();
        
        // Get filter values from the request
        $defaults = $this->getFiltersFromRequest();
        $defaults['query'] = $this->getParameter('query', '');
        
        // Zde vytvoříme formulář s výchozími hodnotami
        $form = $searchForm->create($defaults);
        
        // Set callback for form processing
        $form->onSuccess[] = function (array $data) {
            $this->processSearchForm($data);
        };
        
        return $form;
    }
    
    /**
     * Process search form
     */
    private function processSearchForm(array $data): void
    {
        // Build query parameters
        $params = ['query' => $data['query'] ?? ''];
        
        // Add filters to query parameters
        foreach ($data as $key => $value) {
            if ($key !== 'query' && $key !== 'search' && $value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }
        
        // Redirect to search with parameters
        $this->redirect('Search:advanced', $params);
    }
    
    /**
     * Get filter values from request
     * 
     * @return array
     */
    private function getFiltersFromRequest(): array
    {
        $filters = [];
        
        // Get category filters
        $categoryIds = $this->getParameter('category_ids');
        if ($categoryIds !== null) {
            $filters['category_ids'] = is_array($categoryIds) ? $categoryIds : [$categoryIds];
        }
        
        // Get tag filters
        $tagIds = $this->getParameter('tag_ids');
        if ($tagIds !== null) {
            $filters['tag_ids'] = is_array($tagIds) ? $tagIds : [$tagIds];
        }
        
        // Get rating filter
        $minRating = $this->getParameter('min_rating');
        if ($minRating !== null) {
            $filters['min_rating'] = (float) $minRating;
        }
        
        // Get Kodi version filter
        $kodiVersion = $this->getParameter('kodi_version');
        if ($kodiVersion !== null && $kodiVersion !== '') {
            $filters['kodi_version'] = $kodiVersion;
        }
        
        // Get sorting options
        $sortBy = $this->getParameter('sort_by', 'name');
        $sortDir = $this->getParameter('sort_dir', 'ASC');
        
        $filters['sort_by'] = $sortBy;
        $filters['sort_dir'] = $sortDir;
        
        return $filters;
    }
}