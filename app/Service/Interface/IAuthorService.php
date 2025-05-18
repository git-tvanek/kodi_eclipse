<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Author;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro autory
 * 
 * @extends IBaseService<Author>
 */
interface IAuthorService extends IBaseService
{
    /**
     * Vytvoří nového autora
     * 
     * @param array $data
     * @return int ID vytvořeného autora
     */
    public function create(array $data): int;
    
    /**
     * Aktualizuje existujícího autora
     * 
     * @param int $id
     * @param array $data
     * @return int ID aktualizovaného autora
     * @throws \Exception
     */
    public function update(int $id, array $data): int;
    
    /**
     * Získá autora s jeho doplňky
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array;
    
    /**
     * Najde autory s filtry
     * 
     * @param array $filters
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Author>
     */
    public function findWithFilters(
        array $filters = [], 
        string $sortBy = 'name', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
    
    /**
     * Získá statistiky autora
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array;
    
    /**
     * Získá síť spolupráce
     * 
     * @param int $authorId
     * @param int $depth
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array;
    
    /**
     * Získá nejlepší autory podle metriky
     * 
     * @param string $metric
     * @param int $limit
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array;
}