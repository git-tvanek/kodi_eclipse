<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Addon;
use App\Entity\Screenshot;
use App\Repository\AddonRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\Interface\IFactoryManager;
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
    
    /** @var string */
    private string $uploadsDir;
    
    /**
     * Konstruktor
     */
    public function __construct(
        AddonRepository $addonRepository,
        IFactoryManager $factoryManager,
        string $uploadsDir = 'uploads'
    ) {
        parent::__construct($factoryManager);
        $this->addonRepository = $addonRepository;
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
     * Vytvoří nový doplněk
     * 
     * @param array $data Data pro vytvoření doplňku
     * @return int ID vytvořeného doplňku
     */
    public function createAddon(array $data): int
    {
        $addon = $this->factoryManager->createAddon($data);
        return $this->addonRepository->create($addon);
    }
    
    /**
     * Aktualizuje existující doplněk
     * 
     * @param int $id ID doplňku
     * @param array $data Data pro aktualizaci
     * @return int ID aktualizovaného doplňku
     */
    public function updateAddon(int $id, array $data): int
    {
        $addon = $this->findById($id);
        
        if (!$addon) {
            throw new \Exception("Doplněk s ID $id nebyl nalezen.");
        }
        
        $updatedAddon = $this->factoryManager->getAddonFactory()->createFromExisting($addon, $data, false);
        return $this->addonRepository->update($updatedAddon);
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
            $addon->setIconUrl($iconPath);
        }
        
        if (isset($uploads['fanart']) && $uploads['fanart'] instanceof FileUpload && $uploads['fanart']->isOk()) {
            $fanartPath = $this->processImageUpload($uploads['fanart'], 'fanart');
            $addon->setFanartUrl($fanartPath);
        }
        
        // Zpracování screenshotů
        $processedScreenshots = [];
        if (!empty($uploads['screenshots'])) {
            foreach ($uploads['screenshots'] as $index => $screenshotUpload) {
                if ($screenshotUpload instanceof FileUpload && $screenshotUpload->isOk()) {
                    $screenshotPath = $this->processImageUpload($screenshotUpload, 'screenshots');
                    
                    $screenshotData = [
                        'url' => $screenshotPath,
                        'description' => $screenshots[$index]['description'] ?? null,
                        'sort_order' => $index,
                        'addon' => $addon
                    ];
                    
                    $screenshot = $this->factoryManager->createScreenshot($screenshotData);
                    $processedScreenshots[] = $screenshot;
                }
            }
        }
        
        // Zajištění, že slug je nastaven
        if (empty($addon->getSlug())) {
            $addon->setSlug(Strings::webalize($addon->getName()));
        }
        
        // Uložení do databáze
        if ($addon->getId()) {
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