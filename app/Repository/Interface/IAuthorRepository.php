<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Author;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář autorů
 * 
 * @extends BaseRepositoryInterface<Author>
 */
interface IAuthorRepository extends IBaseRepository
{
    /**
     * Vytvoří nového autora
     * 
     * @param Author $author
     * @return int
     */
    public function create(Author $author): int;
    
    /**
     * Najde autora s jeho doplňky
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array;
    
    /**
     * Najde autory s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Author>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Získá statistiky aktivity autora
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array;
    
    /**
     * Získá síť spolupráce autora
     * 
     * @param int $authorId
     * @param int $depth Maximální hloubka vztahů k prozkoumání
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array;
    
    /**
     * Získá nejlepší autory podle různých metrik
     * 
     * @param string $metric 'addons', 'downloads', nebo 'rating'
     * @param int $limit Maximální počet autorů k vrácení
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array;
}