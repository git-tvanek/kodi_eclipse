// app/Factory/Interface/IFactoryManager.php
<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\Addon;
use App\Entity\AddonReview;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Screenshot;
use App\Entity\Tag;
use App\Entity\User;

/**
 * Rozhraní pro správce továren
 */
interface IFactoryManager
{
    /**
     * Získá továrnu pro vytváření doplňků
     * 
     * @return IAddonFactory
     */
    public function getAddonFactory(): IAddonFactory;
    
    /**
     * Získá továrnu pro vytváření recenzí
     * 
     * @return IAddonReviewFactory
     */
    public function getAddonReviewFactory(): IAddonReviewFactory;
    
    /**
     * Získá továrnu pro vytváření autorů
     * 
     * @return IAuthorFactory
     */
    public function getAuthorFactory(): IAuthorFactory;
    
    /**
     * Získá továrnu pro vytváření kategorií
     * 
     * @return ICategoryFactory
     */
    public function getCategoryFactory(): ICategoryFactory;
    
    /**
     * Získá továrnu pro vytváření screenshotů
     * 
     * @return IScreenshotFactory
     */
    public function getScreenshotFactory(): IScreenshotFactory;
    
    /**
     * Získá továrnu pro vytváření tagů
     * 
     * @return ITagFactory
     */
    public function getTagFactory(): ITagFactory;
    
    /**
     * Získá továrnu pro konkrétní entitu na základě jejího názvu třídy
     * 
     * @param string $entityClass Název třídy entity
     * @return IBaseFactory
     * @throws \InvalidArgumentException Pokud továrna pro entitu neexistuje
     */
    public function getFactoryForEntity(string $entityClass): IBaseFactory;
    
    /**
     * Vytvoří novou instanci doplňku
     * 
     * @param array $data
     * @return Addon
     */
    public function createAddon(array $data): Addon;
    
    /**
     * Vytvoří novou instanci recenze
     * 
     * @param array $data
     * @return AddonReview
     */
    public function createAddonReview(array $data): AddonReview;
    
    /**
     * Vytvoří novou instanci autora
     * 
     * @param array $data
     * @return Author
     */
    public function createAuthor(array $data): Author;
    
    /**
     * Vytvoří novou instanci kategorie
     * 
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category;
    
    /**
     * Vytvoří novou instanci screenshotu
     * 
     * @param array $data
     * @return Screenshot
     */
    public function createScreenshot(array $data): Screenshot;
    
    /**
     * Vytvoří novou instanci tagu
     * 
     * @param array $data
     * @return Tag
     */
    public function createTag(array $data): Tag;
}