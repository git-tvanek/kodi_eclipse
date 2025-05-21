<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Addon;
use App\Entity\AddonReview;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\DownloadLog;
use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\Screenshot;
use App\Entity\Tag;
use App\Entity\User;
use App\Factory\Interface\IAddonFactory;
use App\Factory\Interface\IAddonReviewFactory;
use App\Factory\Interface\IAuthorFactory;
use App\Factory\Interface\IBaseFactory;
use App\Factory\Interface\ICategoryFactory;
use App\Factory\Interface\IDownloadLogFactory;
use App\Factory\Interface\IFactoryManager;
use App\Factory\Interface\IPermissionFactory;
use App\Factory\Interface\IRoleFactory;
use App\Factory\Interface\IScreenshotFactory;
use App\Factory\Interface\ITagFactory;
use App\Factory\Interface\IUserFactory;

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
    
    /** @var IPermissionFactory */
    private IPermissionFactory $permissionFactory;
    
    /** @var IRoleFactory */
    private IRoleFactory $roleFactory;
    
    /** @var IUserFactory */
    private IUserFactory $userFactory;
    
    /** @var IDownloadLogFactory */
    private IDownloadLogFactory $downloadLogFactory;
    
    /** @var array<string, IBaseFactory> Mapa entit na továrny */
    private array $factoryMap = [];
    
    /**
     * Konstruktor
     */
    public function __construct(
        IAddonFactory $addonFactory,
        IAddonReviewFactory $addonReviewFactory,
        IAuthorFactory $authorFactory,
        ICategoryFactory $categoryFactory,
        IScreenshotFactory $screenshotFactory,
        ITagFactory $tagFactory,
        IPermissionFactory $permissionFactory,
        IRoleFactory $roleFactory,
        IUserFactory $userFactory,
        IDownloadLogFactory $downloadLogFactory
    ) {
        $this->addonFactory = $addonFactory;
        $this->addonReviewFactory = $addonReviewFactory;
        $this->authorFactory = $authorFactory;
        $this->categoryFactory = $categoryFactory;
        $this->screenshotFactory = $screenshotFactory;
        $this->tagFactory = $tagFactory;
        $this->permissionFactory = $permissionFactory;
        $this->roleFactory = $roleFactory;
        $this->userFactory = $userFactory;
        $this->downloadLogFactory = $downloadLogFactory;
        
        // Inicializace mapy entit na továrny
        $this->factoryMap = [
            Addon::class => $addonFactory,
            AddonReview::class => $addonReviewFactory,
            Author::class => $authorFactory,
            Category::class => $categoryFactory,
            Screenshot::class => $screenshotFactory,
            Tag::class => $tagFactory,
            Permission::class => $permissionFactory,
            Role::class => $roleFactory,
            User::class => $userFactory,
            DownloadLog::class => $downloadLogFactory
        ];
    }
    
    // Existující metody
    public function getAddonFactory(): IAddonFactory
    {
        return $this->addonFactory;
    }
    
    public function getAddonReviewFactory(): IAddonReviewFactory
    {
        return $this->addonReviewFactory;
    }
    
    public function getAuthorFactory(): IAuthorFactory
    {
        return $this->authorFactory;
    }
    
    public function getCategoryFactory(): ICategoryFactory
    {
        return $this->categoryFactory;
    }
    
    public function getScreenshotFactory(): IScreenshotFactory
    {
        return $this->screenshotFactory;
    }
    
    public function getTagFactory(): ITagFactory
    {
        return $this->tagFactory;
    }
    
    // Nové metody
    public function getPermissionFactory(): IPermissionFactory
    {
        return $this->permissionFactory;
    }
    
    public function getRoleFactory(): IRoleFactory
    {
        return $this->roleFactory;
    }
    
    public function getUserFactory(): IUserFactory
    {
        return $this->userFactory;
    }
    
    public function getDownloadLogFactory(): IDownloadLogFactory
    {
        return $this->downloadLogFactory;
    }
    
    public function getFactoryForEntity(string $entityClass): IBaseFactory
    {
        if (!isset($this->factoryMap[$entityClass])) {
            throw new \InvalidArgumentException("Továrna pro entitu '$entityClass' neexistuje.");
        }
        
        return $this->factoryMap[$entityClass];
    }
    
    // Existující metody pro vytváření entit
    public function createAddon(array $data): Addon
    {
        return $this->addonFactory->create($data);
    }
    
    public function createAddonReview(array $data): AddonReview
    {
        return $this->addonReviewFactory->create($data);
    }
    
    public function createAuthor(array $data): Author
    {
        return $this->authorFactory->create($data);
    }
    
    public function createCategory(array $data): Category
    {
        return $this->categoryFactory->create($data);
    }
    
    public function createScreenshot(array $data): Screenshot
    {
        return $this->screenshotFactory->create($data);
    }
    
    public function createTag(array $data): Tag
    {
        return $this->tagFactory->create($data);
    }
    
    // Nové metody pro vytváření entit
    public function createPermission(array $data): Permission
    {
        return $this->permissionFactory->create($data);
    }
    
    public function createRole(array $data): Role
    {
        return $this->roleFactory->create($data);
    }
    
    public function createUser(array $data): User
    {
        return $this->userFactory->create($data);
    }
    
    public function createDownloadLog(array $data): DownloadLog
    {
        return $this->downloadLogFactory->create($data);
    }
}