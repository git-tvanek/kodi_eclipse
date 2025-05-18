<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Addon;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro doplňky
 * 
 * @extends IBaseService<Addon>
 */
interface IAddonService extends IBaseService
{
    /**
     * Najde doplněk podle slugu
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon;
    
    /**
     * Najde doplňky podle kategorie
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde doplňky podle autora
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Najde populární doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findPopular(int $limit = 10): Collection;
    
    /**
     * Najde nejlépe hodnocené doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findTopRated(int $limit = 10): Collection;
    
    /**
     * Najde nejnovější doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findNewest(int $limit = 10): Collection;
    
    /**
     * Vyhledá doplňky podle klíčového slova
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Zvýší počet stažení
     * 
     * @param int $id
     * @return int Počet ovlivněných řádků
     */
    public function incrementDownloadCount(int $id): int;
    
    /**
     * Uloží doplněk s přidruženými daty
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @param array $uploads Nahrané soubory (screenshoty a ikony)
     * @return int ID doplňku
     * @throws \Exception
     */
    public function saveWithRelated(
        Addon $addon, 
        array $screenshots = [], 
        array $tagIds = [],
        array $uploads = []
    ): int;
    
    /**
     * Získá doplněk s přidruženými daty
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithRelated(int $id): ?array;
    
    /**
     * Najde podobné doplňky
     * 
     * @param int $addonId
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection;
    
    /**
     * Pokročilé vyhledávání
     * 
     * @param string $query
     * @param array $fields
     * @param array $filters
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function advancedSearch(
        string $query, 
        array $fields = ['name', 'description'], 
        array $filters = [], 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
}