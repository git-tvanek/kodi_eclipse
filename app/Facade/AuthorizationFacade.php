<?php

declare(strict_types=1);

namespace App\Facade;

use App\Service\Interface\IAuthorizationService;

/**
 * Fasáda pro autorizaci a kontrolu oprávnění
 */
class AuthorizationFacade implements IFacade
{
    /** @var IAuthorizationService */
    private IAuthorizationService $authorizationService;
    
    /**
     * Konstruktor
     * 
     * @param IAuthorizationService $authorizationService
     */
    public function __construct(IAuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
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
        return $this->authorizationService->isAllowed($userId, $resource, $action);
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
        return $this->authorizationService->hasRole($userId, $roleCode);
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
        return $this->authorizationService->hasAnyRole($userId, $roleCodes);
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
        return $this->authorizationService->hasAllRoles($userId, $roleCodes);
    }
    
    /**
     * Získá všechna oprávnění uživatele
     *
     * @param int $userId ID uživatele
     * @return array Oprávnění uživatele
     */
    public function getUserPermissions(int $userId): array
    {
        return $this->authorizationService->getUserPermissions($userId);
    }
   
}