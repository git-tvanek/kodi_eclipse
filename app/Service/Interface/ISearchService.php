<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Rozhraní služby pro vyhledávání
 */
interface ISearchService
{
    /**
     * Provede jednoduché vyhledávání
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): array;
    
    /**
     * Provede pokročilé vyhledávání
     * 
     * @param string $query
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return array
     */
    public function advancedSearch(
        string $query, 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): array;
}