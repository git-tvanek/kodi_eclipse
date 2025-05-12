<?php

declare(strict_types=1);

namespace App\Facade;

use App\Service\ISearchService;

/**
 * Fasáda pro vyhledávání
 */
class SearchFacade implements IFacade
{
    /** @var ISearchService */
    private ISearchService $searchService;
    
    /**
     * Konstruktor
     * 
     * @param ISearchService $searchService
     */
    public function __construct(ISearchService $searchService)
    {
        $this->searchService = $searchService;
    }
    
    /**
     * Provede základní vyhledávání
     * 
     * @param string $query Vyhledávací dotaz
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return array
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->searchService->search($query, $page, $itemsPerPage);
    }
    
    /**
     * Provede pokročilé vyhledávání
     * 
     * @param string $query Vyhledávací dotaz
     * @param array $filters Filtry pro vyhledávání
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return array
     */
    public function advancedSearch(string $query, array $filters = [], int $page = 1, int $itemsPerPage = 10): array
    {
        return $this->searchService->advancedSearch($query, $filters, $page, $itemsPerPage);
    }
}