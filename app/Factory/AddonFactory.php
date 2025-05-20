<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\Author;
use App\Entity\Category;
use App\Factory\Interface\IAddonFactory;
use Nette\Utils\Strings;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření doplňků
 * 
 * @implements IAddonFactory
 */
class AddonFactory implements IAddonFactory
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Vytvoří novou instanci doplňku
     * 
     * @param array $data
     * @return Addon
     */
    public function create(array $data): Addon
    {
        // Zajištění povinných polí
        if (!isset($data['name'])) {
            throw new \InvalidArgumentException('Addon name is required');
        }
        
        if (!isset($data['version'])) {
            throw new \InvalidArgumentException('Addon version is required');
        }
        
        if (!isset($data['author_id'])) {
            throw new \InvalidArgumentException('Author ID is required');
        }
        
        if (!isset($data['category_id'])) {
            throw new \InvalidArgumentException('Category ID is required');
        }
        
        if (!isset($data['download_url'])) {
            throw new \InvalidArgumentException('Download URL is required');
        }
        
        // Načtení závislých entit
        $author = $this->entityManager->getRepository(Author::class)->find($data['author_id']);
        if (!$author) {
            throw new \InvalidArgumentException('Author not found');
        }
        
        $category = $this->entityManager->getRepository(Category::class)->find($data['category_id']);
        if (!$category) {
            throw new \InvalidArgumentException('Category not found');
        }
        
        // Vytvoření instance
        $addon = new Addon();
        
        // Nastavení základních vlastností
        $addon->setName($data['name']);
        $addon->setVersion($data['version']);
        $addon->setDownloadUrl($data['download_url']);
        
        // Nastavení referencí na entity
        $addon->setAuthor($author);
        $addon->setCategory($category);
        
        // Nastavení slugu
        if (!isset($data['slug'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }
        $addon->setSlug($data['slug']);
        
        // Nastavení nepovinných polí
        if (isset($data['description'])) {
            $addon->setDescription($data['description']);
        }
        
        if (isset($data['repository_url'])) {
            $addon->setRepositoryUrl($data['repository_url']);
        }
        
        if (isset($data['icon_url'])) {
            $addon->setIconUrl($data['icon_url']);
        }
        
        if (isset($data['fanart_url'])) {
            $addon->setFanartUrl($data['fanart_url']);
        }
        
        if (isset($data['kodi_version_min'])) {
            $addon->setKodiVersionMin($data['kodi_version_min']);
        }
        
        if (isset($data['kodi_version_max'])) {
            $addon->setKodiVersionMax($data['kodi_version_max']);
        }
        
        if (isset($data['downloads_count'])) {
            $addon->setDownloadsCount($data['downloads_count']);
        }
        
        if (isset($data['rating'])) {
            $addon->setRating((float)$data['rating']);
        }
        
        // Časové údaje jsou nastaveny v konstruktoru Addon entity,
        // ale můžeme je přepsat, pokud byly explicitně zadány
        if (isset($data['created_at']) && $data['created_at'] instanceof DateTime) {
            $addon->setCreatedAt($data['created_at']);
        }
        
        if (isset($data['updated_at']) && $data['updated_at'] instanceof DateTime) {
            $addon->setUpdatedAt($data['updated_at']);
        }
        
        return $addon;
    }
    
    /**
     * Vytvoří kopii existujícího doplňku s možností přepsání některých hodnot
     * 
     * @param Addon $addon Existující doplněk
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Addon
     */
    public function createFromExisting(Addon $addon, array $overrideData = [], bool $createNew = true): Addon
    {
        if ($createNew) {
            // Vytvoření nové instance
            $newAddon = new Addon();
            
            // Kopírování vlastností ze zdrojové entity
            $newAddon->setName($addon->getName());
            $newAddon->setSlug($addon->getSlug());
            $newAddon->setDescription($addon->getDescription());
            $newAddon->setVersion($addon->getVersion());
            $newAddon->setAuthor($addon->getAuthor());
            $newAddon->setCategory($addon->getCategory());
            $newAddon->setRepositoryUrl($addon->getRepositoryUrl());
            $newAddon->setDownloadUrl($addon->getDownloadUrl());
            $newAddon->setIconUrl($addon->getIconUrl());
            $newAddon->setFanartUrl($addon->getFanartUrl());
            $newAddon->setKodiVersionMin($addon->getKodiVersionMin());
            $newAddon->setKodiVersionMax($addon->getKodiVersionMax());
            $newAddon->setDownloadsCount($addon->getDownloadsCount());
            $newAddon->setRating($addon->getRating());
            // Čas vytvoření je automaticky nastaven v konstruktoru
            $newAddon->setUpdatedAt(new DateTime());
        } else {
            // Přímá úprava existující entity
            $newAddon = $addon;
        }
        
        // Přepsání hodnot
        if (isset($overrideData['name'])) {
            $newAddon->setName($overrideData['name']);
            
            // Pokud byl změněn název a není explicitně uveden slug, vygenerovat nový
            if (!isset($overrideData['slug'])) {
                $newAddon->setSlug(Strings::webalize($overrideData['name']));
            }
        }
        
        if (isset($overrideData['slug'])) {
            $newAddon->setSlug($overrideData['slug']);
        }
        
        if (isset($overrideData['description'])) {
            $newAddon->setDescription($overrideData['description']);
        }
        
        if (isset($overrideData['version'])) {
            $newAddon->setVersion($overrideData['version']);
        }
        
        if (isset($overrideData['author_id'])) {
            $author = $this->entityManager->getRepository(Author::class)->find($overrideData['author_id']);
            if ($author) {
                $newAddon->setAuthor($author);
            }
        }
        
        if (isset($overrideData['category_id'])) {
            $category = $this->entityManager->getRepository(Category::class)->find($overrideData['category_id']);
            if ($category) {
                $newAddon->setCategory($category);
            }
        }
        
        if (isset($overrideData['repository_url'])) {
            $newAddon->setRepositoryUrl($overrideData['repository_url']);
        }
        
        if (isset($overrideData['download_url'])) {
            $newAddon->setDownloadUrl($overrideData['download_url']);
        }
        
        if (isset($overrideData['icon_url'])) {
            $newAddon->setIconUrl($overrideData['icon_url']);
        }
        
        if (isset($overrideData['fanart_url'])) {
            $newAddon->setFanartUrl($overrideData['fanart_url']);
        }
        
        if (isset($overrideData['kodi_version_min'])) {
            $newAddon->setKodiVersionMin($overrideData['kodi_version_min']);
        }
        
        if (isset($overrideData['kodi_version_max'])) {
            $newAddon->setKodiVersionMax($overrideData['kodi_version_max']);
        }
        
        if (isset($overrideData['downloads_count'])) {
            $newAddon->setDownloadsCount($overrideData['downloads_count']);
        }
        
        if (isset($overrideData['rating'])) {
            $newAddon->setRating((float)$overrideData['rating']);
        }
        
        // Aktualizace času úpravy
        $newAddon->setUpdatedAt(new DateTime());
        
        return $newAddon;
    }
    
    /**
     * Vytvoří základní doplněk s minimálními povinnými daty
     * 
     * @param string $name Název doplňku
     * @param string $version Verze doplňku
     * @param int $authorId ID autora
     * @param int $categoryId ID kategorie
     * @param string $downloadUrl URL pro stažení doplňku
     * @return Addon
     */
    public function createBase(
        string $name,
        string $version,
        int $authorId,
        int $categoryId,
        string $downloadUrl
    ): Addon {
        return $this->create([
            'name' => $name,
            'version' => $version,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'download_url' => $downloadUrl
        ]);
    }
}