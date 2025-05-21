<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\PermissionRepository;
use App\Service\Interface\IAuthorizationService;
use App\Factory\Interface\IFactoryManager;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

/**
 * Implementace autorizační služby
 */
class AuthorizationService implements IAuthorizationService
{
    /** @var UserRepository */
    private UserRepository $userRepository;
    
    /** @var RoleRepository */
    private RoleRepository $roleRepository;
    
    /** @var PermissionRepository */
    private PermissionRepository $permissionRepository;
    
    /** @var IFactoryManager */
    private IFactoryManager $factoryManager;
    
    /** @var Cache|null */
    private ?Cache $cache;
    
    /**
     * Konstruktor
     *
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     * @param PermissionRepository $permissionRepository
     * @param IFactoryManager $factoryManager
     * @param Storage|null $cacheStorage
     */
    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        IFactoryManager $factoryManager,
        ?Storage $cacheStorage = null
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->factoryManager = $factoryManager;
        $this->cache = $cacheStorage ? new Cache($cacheStorage, 'authorization') : null;
    }
    
    /**
     * Kontroluje, zda má uživatel oprávnění k akci na zdroji
     *
     * @param int $userId ID uživatele
     * @param string $resource Zdroj
     * @param string $action Akce
     * @return bool
     */
    public function isAllowed(int $userId, string $resource, string $action): bool
    {
        // Použití cache pro zrychlení autorizace
        $cacheKey = "auth_check_{$userId}_{$resource}_{$action}";
        
        if ($this->cache) {
            $result = $this->cache->load($cacheKey);
            if ($result !== null) {
                return (bool)$result;
            }
        }
        
        // Získání oprávnění uživatele
        $permissions = $this->permissionRepository->findByUser($userId);
        
        // Kontrola oprávnění
        foreach ($permissions as $permission) {
            if ($permission->getResource() === $resource && $permission->getAction() === $action) {
                // Oprávnění nalezeno
                if ($this->cache) {
                    $this->cache->save($cacheKey, true, [
                        Cache::Expire => '30 minutes',
                        'userId' => $userId
                    ]);
                }
                return true;
            }
        }
        
        // Oprávnění nenalezeno
        if ($this->cache) {
            $this->cache->save($cacheKey, false, [
                Cache::Expire => '30 minutes',
                'userId' => $userId
            ]);
        }
        return false;
    }
    
    /**
     * Kontroluje, zda má uživatel roli
     *
     * @param int $userId ID uživatele
     * @param string $roleCode Kód role
     * @return bool
     */
    public function hasRole(int $userId, string $roleCode): bool
    {
        // Použití cache pro zrychlení
        $cacheKey = "has_role_{$userId}_{$roleCode}";
        
        if ($this->cache) {
            $result = $this->cache->load($cacheKey);
            if ($result !== null) {
                return (bool)$result;
            }
        }
        
        // Získání role podle kódu
        $role = $this->roleRepository->findByCode($roleCode);
        if (!$role) {
            return false;
        }
        
        // Získání rolí uživatele
        $userRoles = $this->roleRepository->findRolesByUser($userId);
        
        // Kontrola, zda uživatel má danou roli
        foreach ($userRoles as $userRole) {
            if ($userRole->getId() === $role->getId()) {
                if ($this->cache) {
                    $this->cache->save($cacheKey, true, [
                        Cache::Expire => '30 minutes',
                        'userId' => $userId
                    ]);
                }
                return true;
            }
        }
        
        if ($this->cache) {
            $this->cache->save($cacheKey, false, [
                Cache::Expire => '30 minutes',
                'userId' => $userId
            ]);
        }
        return false;
    }
    
    /**
     * Kontroluje, zda má uživatel alespoň jednu roli z poskytnutého seznamu
     *
     * @param int $userId ID uživatele
     * @param array $roleCodes Pole kódů rolí
     * @return bool
     */
    public function hasAnyRole(int $userId, array $roleCodes): bool
    {
        foreach ($roleCodes as $roleCode) {
            if ($this->hasRole($userId, $roleCode)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Kontroluje, zda má uživatel všechny role z poskytnutého seznamu
     *
     * @param int $userId ID uživatele
     * @param array $roleCodes Pole kódů rolí
     * @return bool
     */
    public function hasAllRoles(int $userId, array $roleCodes): bool
    {
        foreach ($roleCodes as $roleCode) {
            if (!$this->hasRole($userId, $roleCode)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Získá všechna oprávnění uživatele
     *
     * @param int $userId ID uživatele
     * @return array Oprávnění uživatele
     */
    public function getUserPermissions(int $userId): array
    {
        // Použití cache pro zrychlení
        $cacheKey = "user_permissions_{$userId}";
        
        if ($this->cache) {
            $result = $this->cache->load($cacheKey);
            if ($result !== null) {
                return $result;
            }
        }
        
        $permissions = $this->permissionRepository->findByUser($userId);
        $result = [];
        
        foreach ($permissions as $permission) {
            $result[] = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
                'resource' => $permission->getResource(),
                'action' => $permission->getAction(),
                'description' => $permission->getDescription()
            ];
        }
        
        if ($this->cache) {
            $this->cache->save($cacheKey, $result, [
                Cache::Expire => '30 minutes',
                'userId' => $userId
            ]);
        }
        
        return $result;
    }
    
    /**
     * Vymaže cache pro uživatele
     *
     * @param int $userId ID uživatele
     */
    public function clearUserCache(int $userId): void
    {
        if ($this->cache) {
            $this->cache->clean([
                Cache::Tags => ['userId' => $userId]
            ]);
        }
    }
}