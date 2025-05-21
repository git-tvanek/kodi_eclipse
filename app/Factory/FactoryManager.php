<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\AddonReview;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Screenshot;
use App\Entity\Tag;
use App\Entity\User;
use App\Factory\Interface\IAddonFactory;
use App\Factory\Interface\IAddonReviewFactory;
use App\Factory\Interface\IAuthorFactory;
use App\Factory\Interface\IBaseFactory;
use App\Factory\Interface\ICategoryFactory;
use App\Factory\Interface\IFactoryManager;
use App\Factory\Interface\IScreenshotFactory;
use App\Factory\Interface\ITagFactory;

/**
 * Implementace správce továren
 */
class FactoryManager implements IFactoryManager
{
    /** @var IAddonFactory */
    private IAddonFactory $addonFactory;
    
    /** @var IAddonReviewFactory */
    private IAddonReviewFactory $addonReviewFactory;
    
    /** @var IAuthorFactory */
    private IAuthorFactory $authorFactory;
    
    /** @var ICategoryFactory */
    private ICategoryFactory $categoryFactory;
    
    /** @var IScreenshotFactory */
    private IScreenshotFactory $screenshotFactory;
    
    /** @var ITagFactory */
    private ITagFactory $tagFactory;
    
    /** @var array<string, IBaseFactory> Mapa entit na továrny */
    private array $factoryMap = [];
    
    /**
     * Konstruktor
     * 
     * @param IAddonFactory $addonFactory
     * @param IAddonReviewFactory $addonReviewFactory
     * @param IAuthorFactory $authorFactory
     * @param ICategoryFactory $categoryFactory
     * @param IScreenshotFactory $screenshotFactory
     * @param ITagFactory $tagFactory
     */
    public function __construct(
        IAddonFactory $addonFactory,
        IAddonReviewFactory $addonReviewFactory,
        IAuthorFactory $authorFactory,
        ICategoryFactory $categoryFactory,
        IScreenshotFactory $screenshotFactory,
        ITagFactory $tagFactory
    ) {
        $this->addonFactory = $addonFactory;
        $this->addonReviewFactory = $addonReviewFactory;
        $this->authorFactory = $authorFactory;
        $this->categoryFactory = $categoryFactory;
        $this->screenshotFactory = $screenshotFactory;
        $this->tagFactory = $tagFactory;
        
        // Inicializace mapy entit na továrny
        $this->factoryMap = [
            Addon::class => $addonFactory,
            AddonReview::class => $addonReviewFactory,
            Author::class => $authorFactory,
            Category::class => $categoryFactory,
            Screenshot::class => $screenshotFactory,
            Tag::class => $tagFactory
        ];
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAddonFactory(): IAddonFactory
    {
        return $this->addonFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAddonReviewFactory(): IAddonReviewFactory
    {
        return $this->addonReviewFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getAuthorFactory(): IAuthorFactory
    {
        return $this->authorFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getCategoryFactory(): ICategoryFactory
    {
        return $this->categoryFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getScreenshotFactory(): IScreenshotFactory
    {
        return $this->screenshotFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getTagFactory(): ITagFactory
    {
        return $this->tagFactory;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getFactoryForEntity(string $entityClass): IBaseFactory
    {
        if (!isset($this->factoryMap[$entityClass])) {
            throw new \InvalidArgumentException("Továrna pro entitu '$entityClass' neexistuje.");
        }
        
        return $this->factoryMap[$entityClass];
    }
    
    /**
     * {@inheritDoc}
     */
    public function createAddon(array $data): Addon
    {
        return $this->addonFactory->create($data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createAddonReview(array $data): AddonReview
    {
        return $this->addonReviewFactory->create($data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createAuthor(array $data): Author
    {
        return $this->authorFactory->create($data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createCategory(array $data): Category
    {
        return $this->categoryFactory->create($data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createScreenshot(array $data): Screenshot
    {
        return $this->screenshotFactory->create($data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createTag(array $data): Tag
    {
        return $this->tagFactory->create($data);
    }
}