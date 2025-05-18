<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Factory\Interface\IAddonFactory;
use Nette\Utils\Strings;
use DateTime;

/**
 * Továrna pro vytváření doplňků
 * 
 * @implements IFactory<Addon>
 */
class AddonFactory implements IAddonFactory
{
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

        // Výchozí hodnoty pro nepovinná pole
        if (!isset($data['slug']) && isset($data['name'])) {
            $data['slug'] = Strings::webalize($data['name']);
        }

        $data['description'] = $data['description'] ?? null;
        $data['repository_url'] = $data['repository_url'] ?? null;
        $data['icon_url'] = $data['icon_url'] ?? null;
        $data['fanart_url'] = $data['fanart_url'] ?? null;
        $data['kodi_version_min'] = $data['kodi_version_min'] ?? null;
        $data['kodi_version_max'] = $data['kodi_version_max'] ?? null;
        $data['downloads_count'] = $data['downloads_count'] ?? 0;
        $data['rating'] = $data['rating'] ?? 0.0;
        
        // Časové údaje
        $data['created_at'] = $data['created_at'] ?? new DateTime();
        $data['updated_at'] = $data['updated_at'] ?? new DateTime();
        
        return Addon::fromArray($data);
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
        $data = $addon->toArray();
        
        // Přepsat data novými hodnotami
        foreach ($overrideData as $key => $value) {
            $data[$key] = $value;
        }
        
        // Pokud byl změněn název a není explicitně uveden slug, vygenerovat nový
        if (isset($overrideData['name']) && !isset($overrideData['slug'])) {
            $data['slug'] = Strings::webalize($overrideData['name']);
        }
        
        // Aktualizovat datum
        $data['updated_at'] = new DateTime();
        
        // Při vytváření nové instance odstranit ID
        if ($createNew) {
            unset($data['id']);
        }
        
        return Addon::fromArray($data);
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