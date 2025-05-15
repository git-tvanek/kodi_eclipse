<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Author;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář autorů
 * 
 * @extends IBaseRepository<Author>
 */
interface IAuthorRepository extends IBaseRepository
{
    /**
     * Vytvoří nového autora
     * 
     * @param Author $author Entita autora k vytvoření
     * @return int ID vytvořeného autora
     */
    public function create(Author $author): int;
    
    /**
     * Vrátí autora s jeho doplňky
     * 
     * @param int $id ID autora
     * @return array|null Pole obsahující autora a jeho doplňky, nebo null pokud autor neexistuje
     */
    public function getWithAddons(int $id): ?array;
    
    /**
     * Vyhledá autory podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů pro vyhledávání
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Author> Stránkovaná kolekce autorů
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Získá statistiky autora a jeho doplňků
     * 
     * @param int $authorId ID autora
     * @return array Statistiky autora nebo prázdné pole, pokud autor neexistuje
     */
    public function getAuthorStatistics(int $authorId): array;
    
    /**
     * Vrátí autory seřazené podle zvoleného kritéria
     * 
     * @param string $metric Kritérium ('addons', 'downloads', 'rating')
     * @param int $limit Maximální počet autorů
     * @return array Pole s nejlepšími autory podle zvoleného kritéria
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array;
    
    /**
     * Vytvoří síť autorů spolupracujících prostřednictvím podobných tagů
     * 
     * @param int $authorId ID autora
     * @param int $depth Hloubka prohledávání sítě
     * @return array Struktura sítě spolupracujících autorů
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array;
}