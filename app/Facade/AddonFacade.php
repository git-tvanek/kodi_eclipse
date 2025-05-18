<?php

declare(strict_types=1);

namespace App\Facade;

use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Entity\Addon;
use App\Service\IAddonService;
use App\Service\ICategoryService;
use App\Service\ITagService;
use App\Service\IAuthorService;

/**
 * Fasáda pro práci s doplňky
 */
class AddonFacade implements IFacade
{
    /** @var IAddonService */
    private IAddonService $addonService;
    
    /** @var ICategoryService */
    private ICategoryService $categoryService;
    
    /** @var ITagService */
    private ITagService $tagService;
    
    /** @var IAuthorService */
    private IAuthorService $authorService;
    
    /**
     * Konstruktor
     * 
     * @param IAddonService $addonService
     * @param ICategoryService $categoryService
     * @param ITagService $tagService
     * @param IAuthorService $authorService
     */
    public function __construct(
        IAddonService $addonService,
        ICategoryService $categoryService,
        ITagService $tagService,
        IAuthorService $authorService
    ) {
        $this->addonService = $addonService;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
        $this->authorService = $authorService;
    }
    
    /**
     * Získá detail doplňku podle ID nebo slugu
     * 
     * @param int|string $idOrSlug
     * @return array|null
     */
    public function getAddonDetail($idOrSlug): ?array
    {
        // Získání instance doplňku podle ID nebo slugu
        $addon = is_numeric($idOrSlug) 
            ? $this->addonService->findById((int)$idOrSlug)
            : $this->addonService->findBySlug((string)$idOrSlug);
            
        if (!$addon) {
            return null;
        }
        
        // Získání detailních dat o doplňku
        $addonDetail = $this->addonService->getWithRelated($addon->id);
        
        // Získání souvisejících doplňků
        $similarAddons = $this->addonService->findSimilarAddons($addon->id, 5);
        
        // Získání cesty kategorie (breadcrumbs)
        $categoryPath = $this->categoryService->getCategoryPath($addon->category_id);
        
        // Přidání dat do výsledku
        $addonDetail['similar_addons'] = $similarAddons;
        $addonDetail['category_path'] = $categoryPath;
        
        return $addonDetail;
    }
    
    /**
     * Vyhledá doplňky s pokročilými filtry
     * 
     * @param string $query Hledaný výraz
     * @param array $filters Filtry
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Addon>
     */
    public function searchAddons(string $query, array $filters = [], int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonService->advancedSearch($query, ['name', 'description'], $filters, $page, $itemsPerPage);
    }
    
    /**
     * Vytvoří nový doplněk
     * 
     * @param array $data Data doplňku
     * @param array $files Nahrané soubory
     * @return int ID vytvořeného doplňku
     */
    public function createAddon(array $data, array $files = []): int
    {
        // Zpracování tagů
        $tagIds = [];
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagName) {
                $tagIds[] = $this->tagService->findOrCreate($tagName);
            }
        }
        
        // Vytvoření instance doplňku
        $addon = new Addon();
        
        // Naplnění doplňku daty
        foreach ($data as $key => $value) {
            if (property_exists($addon, $key)) {
                $addon->$key = $value;
            }
        }
        
        // Příprava screenshotů
        $screenshots = isset($data['screenshots']) ? $data['screenshots'] : [];
        
        // Uložení doplňku
        return $this->addonService->saveWithRelated($addon, $screenshots, $tagIds, $files);
    }
    
    /**
     * Aktualizuje existující doplněk
     * 
     * @param int $addonId ID doplňku
     * @param array $data Data doplňku
     * @param array $files Nahrané soubory
     * @return int ID aktualizovaného doplňku
     * @throws \Exception Pokud doplněk neexistuje
     */
    public function updateAddon(int $addonId, array $data, array $files = []): int
    {
        // Získání instance doplňku
        $addon = $this->addonService->findById($addonId);
        
        if (!$addon) {
            throw new \Exception("Doplněk s ID {$addonId} neexistuje.");
        }
        
        // Zpracování tagů
        $tagIds = [];
        if (isset($data['tags']) && is_array($data['tags'])) {
            foreach ($data['tags'] as $tagName) {
                $tagIds[] = $this->tagService->findOrCreate($tagName);
            }
        }
        
        // Aktualizace dat doplňku
        foreach ($data as $key => $value) {
            if (property_exists($addon, $key) && $key !== 'id') {
                $addon->$key = $value;
            }
        }
        
        // Příprava screenshotů
        $screenshots = isset($data['screenshots']) ? $data['screenshots'] : [];
        
        // Uložení doplňku
        return $this->addonService->saveWithRelated($addon, $screenshots, $tagIds, $files);
    }
    
    /**
     * Získá URL pro stažení doplňku a inkrementuje počítadlo stažení
     * 
     * @param int|string $idOrSlug ID nebo slug doplňku
     * @return string|null URL pro stažení
     */
    public function getDownloadUrl($idOrSlug): ?string
    {
        $addon = is_numeric($idOrSlug) 
            ? $this->addonService->findById((int)$idOrSlug)
            : $this->addonService->findBySlug((string)$idOrSlug);
            
        if (!$addon) {
            return null;
        }
        
        // Zvýšení počítadla stažení
        $this->addonService->incrementDownloadCount($addon->id);
        
        return $addon->download_url;
    }
    
    /**
     * Získá populární doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function getPopularAddons(int $limit = 10): Collection
    {
        return $this->addonService->findPopular($limit);
    }
    
    /**
     * Získá nejlépe hodnocené doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function getTopRatedAddons(int $limit = 10): Collection
    {
        return $this->addonService->findTopRated($limit);
    }
    
    /**
     * Získá nejnovější doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function getNewestAddons(int $limit = 10): Collection
    {
        return $this->addonService->findNewest($limit);
    }
    
    /**
     * Smaže doplněk
     * 
     * @param int $addonId
     * @return bool
     */
    public function deleteAddon(int $addonId): bool
    {
        return $this->addonService->delete($addonId);
    }

}