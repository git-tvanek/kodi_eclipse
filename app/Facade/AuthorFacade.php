<?php

declare(strict_types=1);

namespace App\Facade;

use App\Collection\PaginatedCollection;
use App\Entity\Author;
use App\Service\IAuthorService;

/**
 * Fasáda pro práci s autory
 */
class AuthorFacade implements IFacade
{
    /** @var IAuthorService */
    private IAuthorService $authorService;
    
    /**
     * Konstruktor
     * 
     * @param IAuthorService $authorService
     */
    public function __construct(IAuthorService $authorService)
    {
        $this->authorService = $authorService;
    }
    
    /**
     * Vytvoří nového autora
     * 
     * @param string $name Jméno autora
     * @param string|null $email E-mail autora
     * @param string|null $website Web autora
     * @return int ID vytvořeného autora
     */
    public function createAuthor(string $name, ?string $email = null, ?string $website = null): int
    {
        $data = [
            'name' => $name,
            'email' => $email,
            'website' => $website
        ];
        
        return $this->authorService->create($data);
    }
    
    /**
     * Aktualizuje existujícího autora
     * 
     * @param int $authorId ID autora
     * @param array $data Data autora
     * @return int ID aktualizovaného autora
     * @throws \Exception Pokud autor neexistuje
     */
    public function updateAuthor(int $authorId, array $data): int
    {
        return $this->authorService->update($authorId, $data);
    }
    
    /**
     * Získá autora podle ID
     * 
     * @param int $authorId ID autora
     * @return Author|null
     */
    public function getAuthor(int $authorId): ?Author
    {
        return $this->authorService->findById($authorId);
    }
    
    /**
     * Získá autora s jeho doplňky
     * 
     * @param int $authorId ID autora
     * @return array|null
     */
    public function getAuthorWithAddons(int $authorId): ?array
    {
        return $this->authorService->getWithAddons($authorId);
    }
    
    /**
     * Najde autory podle filtrů
     * 
     * @param array $filters Filtry
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Author>
     */
    public function findAuthors(array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->authorService->findWithFilters($filters, 'name', 'ASC', $page, $itemsPerPage);
    }
    
    /**
     * Získá statistiky autora
     * 
     * @param int $authorId ID autora
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array
    {
        return $this->authorService->getAuthorStatistics($authorId);
    }
    
    /**
     * Získá síť spolupráce autora
     * 
     * @param int $authorId ID autora
     * @param int $depth Hloubka sítě
     * @return array
     */
    public function getAuthorCollaborationNetwork(int $authorId, int $depth = 2): array
    {
        return $this->authorService->getCollaborationNetwork($authorId, $depth);
    }
    
    /**
     * Získá nejlepší autory podle metriky
     * 
     * @param string $metric Metrika ('addons', 'downloads', 'rating')
     * @param int $limit Počet autorů
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        return $this->authorService->getTopAuthors($metric, $limit);
    }
    
    /**
     * Smaže autora
     * 
     * @param int $authorId ID autora
     * @return bool
     */
    public function deleteAuthor(int $authorId): bool
    {
        return $this->authorService->delete($authorId);
    }
}