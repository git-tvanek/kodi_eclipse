<?php

declare(strict_types=1);

namespace App\Factory\Interface;

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

/**
 * Rozhraní pro správce továren
 */
interface IFactoryManager
{
    // Existující metody
    public function getAddonFactory(): IAddonFactory;
    public function getAddonReviewFactory(): IAddonReviewFactory;
    public function getAuthorFactory(): IAuthorFactory;
    public function getCategoryFactory(): ICategoryFactory;
    public function getScreenshotFactory(): IScreenshotFactory;
    public function getTagFactory(): ITagFactory;
    
    // Nové metody
    public function getPermissionFactory(): IPermissionFactory;
    public function getRoleFactory(): IRoleFactory;
    public function getUserFactory(): IUserFactory;
    public function getDownloadLogFactory(): IDownloadLogFactory;
    
    public function getFactoryForEntity(string $entityClass): IBaseFactory;
    
    // Existující metody pro vytváření entit
    public function createAddon(array $data): Addon;
    public function createAddonReview(array $data): AddonReview;
    public function createAuthor(array $data): Author;
    public function createCategory(array $data): Category;
    public function createScreenshot(array $data): Screenshot;
    public function createTag(array $data): Tag;
    
    // Nové metody pro vytváření entit
    public function createPermission(array $data): Permission;
    public function createRole(array $data): Role;
    public function createUser(array $data): User;
    public function createDownloadLog(array $data): DownloadLog;
}