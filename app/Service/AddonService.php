<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Addon;
use App\Entity\Screenshot;
use App\Repository\AddonRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\AddonFactory;
use App\Factory\ScreenshotFactory;
use Nette\Utils\Strings;
use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;

/**
 * Implementace služby pro doplňky
 * 
 * @extends BaseService<Addon>
 * @implements IAddonService
 */
class AddonService extends BaseService implements IAddonService
{
    /** @var AddonRepository */
    private AddonRepository $addonRepository;
    
    /** @var AddonFactory */
    private AddonFactory $addonFactory;
    
    /** @var ScreenshotFactory */
    private ScreenshotFactory $screenshotFactory;
    
    /** @var string */
    private string $uploadsDir;
    
    /**
     * Konstruktor
     * 
     * @param AddonRepository $addonRepository
     * @param AddonFactory $addonFactory
     * @param ScreenshotFactory $screenshotFactory
     * @param string $uploadsDir
     */
    public function __construct(
        AddonRepository $addonRepository,
        AddonFactory $addonFactory,
        ScreenshotFactory $screenshotFactory,
        string $uploadsDir = 'uploads'
    ) {
        parent::__construct();
        $this->addonRepository = $addonRepository;
        $this->addonFactory = $addonFactory;
        $this->screenshotFactory = $screenshotFactory;
        $this->entityClass = Addon::class;
        $this->uploadsDir = $uploadsDir;
    }
    
    /**
     * Získá repozitář pro entitu
     * 
     * @return AddonRepository
     */
    protected function getRepository(): AddonRepository
    {
        return $this->addonRepository;
    }
    
    /**
     * Najde doplněk podle slugu
     * 
     * @param string $slug
     * @return Addon|null
     */
    public function findBySlug(string $slug): ?Addon
    {
        return $this->addonRepository->findBySlug($slug);
    }
    
    /**
     * Najde doplňky podle kategorie
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByCategory(int $categoryId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->findByCategory($categoryId, $page, $itemsPerPage);
    }
    
    /**
     * Najde doplňky podle autora
     * 
     * @param int $authorId
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function findByAuthor(int $authorId, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->findByAuthor($authorId, $page, $itemsPerPage);
    }
    
    /**
     * Najde populární doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findPopular(int $limit = 10): Collection
    {
        return $this->addonRepository->findPopular($limit);
    }
    
    /**
     * Najde nejlépe hodnocené doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findTopRated(int $limit = 10): Collection
    {
        return $this->addonRepository->findTopRated($limit);
    }
    
    /**
     * Najde nejnovější doplňky
     * 
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findNewest(int $limit = 10): Collection
    {
        return $this->addonRepository->findNewest($limit);
    }
    
    /**
     * Vyhledá doplňky podle klíčového slova
     * 
     * @param string $query
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Addon>
     */
    public function search(string $query, int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->addonRepository->search($query, $page, $itemsPerPage);
    }
    
    /**
     * Zvýší počet stažení
     * 
     * @param int $id
     * @return int Počet ovlivněných řádků
     */
    public function incrementDownloadCount(int $id): int
    {
        return $this->addonRepository->incrementDownloadCount($id);
    }
    
    /**
     * Uloží doplněk s přidruženými daty
     * 
     * @param Addon $addon
     * @param array $screenshots
     * @param array $tagIds
     * @param array $uploads Nahrané soubory (screenshoty a ikony)
     * @return int
     * @throws \Exception
     */
    public function saveWithRelated(
        Addon $addon, 
        array $screenshots = [], 
        array $tagIds = [],
        array $uploads = []
    ): int {
        // Zpracování nahraných souborů
        if (isset($uploads['icon']) && $uploads['icon'] instanceof FileUpload && $uploads['icon']->isOk()) {
            $iconPath = $this->processImageUpload($uploads['icon'], 'icons');
            $addon->icon_url = $iconPath;
        }
        
        if (isset($uploads['fanart']) && $uploads['fanart'] instanceof FileUpload && $uploads['fanart']->isOk()) {
            $fanartPath = $this->processImageUpload($uploads['fanart'], 'fanart');
            $addon->fanart_url = $fanartPath;
        }
        
        // Zpracování screenshotů
        $processedScreenshots = [];
        if (!empty($uploads['screenshots'])) {
            foreach ($uploads['screenshots'] as $index => $screenshotUpload) {
                if ($screenshotUpload instanceof FileUpload && $screenshotUpload->isOk()) {
                    $screenshotPath = $this->processImageUpload($screenshotUpload, 'screenshots');
                    
                    $screenshot = new Screenshot();
                    $screenshot->url = $screenshotPath;
                    $screenshot->description = $screenshots[$index]['description'] ?? null;
                    $screenshot->sort_order = $index;
                    
                    $processedScreenshots[] = $screenshot;
                }
            }
        }
        
        // Zajištění, že slug je nastaven
        if (empty($addon->slug)) {
            $addon->slug = Strings::webalize($addon->name);
        }
        
        // Uložení do databáze
        if (isset($addon->id)) {
            return $this->addonRepository->updateWithRelated($addon, $processedScreenshots, $tagIds);
        } else {
            return $this->addonRepository->createWithRelated($addon, $processedScreenshots, $tagIds);
        }
    }
    
    /**
     * Získá doplněk s přidruženými daty
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithRelated(int $id): ?array
    {
        return $this->addonRepository->getWithRelated($id);
    }
    
    /**
     * Najde podobné doplňky
     * 
     * @param int $addonId
     * @param int $limit
     * @return Collection<Addon>
     */
    public function findSimilarAddons(int $addonId, int $limit = 5): Collection
    {
        return $this->addonRepository->findSimilarAddons($addonId, $limit);
    }
    
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
    ): PaginatedCollection {
        return $this->addonRepository->advancedSearch($query, $fields, $filters, $page, $itemsPerPage);
    }
    
    /**
     * Zpracuje nahraný obrázek
     * 
     * @param FileUpload $file
     * @param string $subdir
     * @return string
     * @throws \Exception
     */
    private function processImageUpload(FileUpload $file, string $subdir): string
    {
        // Kontrola, zda jde o obrázek
        if (!$file->isImage()) {
            throw new \Exception('Nahraný soubor není obrázek');
        }
        
        // Vytvoření cílového adresáře, pokud neexistuje
        $dir = $this->uploadsDir . '/' . $subdir;
        if (!is_dir($dir)) {
            FileSystem::createDir($dir);
        }
        
        // Generování unikátního názvu souboru
        $ext = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
        $filename = md5(uniqid('', true)) . '.' . $ext;
        $filepath = $dir . '/' . $filename;
        
        // Uložení souboru
        $file->move($filepath);
        
        // Vrácení relativní cesty pro uložení
        return $subdir . '/' . $filename;
    }
}