<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Addon;

/**
 * Rozhraní pro továrnu doplňků
 * 
 * @extends IFactory<Addon>
 */
interface IAddonFactory extends IFactory
{
    /**
     * Vytvoří novou instanci doplňku
     * 
     * @param array $data
     * @return Addon
     */
    public function create(array $data): Addon;
    
    /**
     * Vytvoří kopii existujícího doplňku s možností přepsání některých hodnot
     * 
     * @param Addon $addon Existující doplněk
     * @param array $overrideData Data k přepsání
     * @param bool $createNew Vytvořit novou instanci (bez ID)
     * @return Addon
     */
    public function createFromExisting(Addon $addon, array $overrideData = [], bool $createNew = true): Addon;
    
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
    ): Addon;
}