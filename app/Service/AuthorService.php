<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Author;
use App\Repository\AuthorRepository;
use App\Collection\PaginatedCollection;
use App\Factory\AuthorFactory;

/**
 * Implementace služby pro autory
 * 
 * @extends BaseService<Author>
 * @implements IAuthorService
 */
class AuthorService extends BaseService implements IAuthorService
{
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;
    
    /** @var AuthorFactory */
    private AuthorFactory $authorFactory;
    
    /**
     * Konstruktor
     * 
     * @param AuthorRepository $authorRepository
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        AuthorRepository $authorRepository,
        AuthorFactory $authorFactory
    ) {
        parent::__construct();
        $this->authorRepository = $authorRepository;
        $this->authorFactory = $authorFactory;
        $this->entityClass = Author::class;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return AuthorRepository
     */
    protected function getRepository(): AuthorRepository
    {
        return $this->authorRepository;
    }
    
    /**
     * Vytvoří nového autora
     * 
     * @param array $data
     * @return int ID vytvořeného autora
     */
    public function create(array $data): int
    {
        $author = $this->authorFactory->create($data);
        return $this->authorRepository->create($author);
    }
    
    /**
     * Aktualizuje existujícího autora
     * 
     * @param int $id
     * @param array $data
     * @return int ID aktualizovaného autora
     * @throws \Exception
     */
    public function update(int $id, array $data): int
    {
        $author = $this->findById($id);
        
        if (!$author) {
            throw new \Exception("Autor s ID {$id} nebyl nalezen.");
        }
        
        $updatedAuthor = $this->authorFactory->createFromExisting($author, $data, false);
        return $this->save($updatedAuthor);
    }
    
    /**
     * Získá autora s jeho doplňky
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithAddons(int $id): ?array
    {
        return $this->authorRepository->getWithAddons($id);
    }
    
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
    ): PaginatedCollection {
        return $this->authorRepository->findWithFilters(
            $filters, 
            $sortBy, 
            $sortDir, 
            $page, 
            $itemsPerPage
        );
    }
    
    /**
     * Získá statistiky autora
     * 
     * @param int $authorId
     * @return array
     */
    public function getAuthorStatistics(int $authorId): array
    {
        return $this->authorRepository->getAuthorStatistics($authorId);
    }
    
    /**
     * Získá síť spolupráce
     * 
     * @param int $authorId
     * @param int $depth
     * @return array
     */
    public function getCollaborationNetwork(int $authorId, int $depth = 2): array
    {
        return $this->authorRepository->getCollaborationNetwork($authorId, $depth);
    }
    
    /**
     * Získá nejlepší autory podle metriky
     * 
     * @param string $metric
     * @param int $limit
     * @return array
     */
    public function getTopAuthors(string $metric = 'addons', int $limit = 10): array
    {
        return $this->authorRepository->getTopAuthors($metric, $limit);
    }
}