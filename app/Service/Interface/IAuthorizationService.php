<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Model\User;

/**
 * Rozhraní pro autorizační službu
 */
interface IAuthorizationService
{
    /**
     * Kontroluje, zda má uživatel oprávnění k akci na zdroji
     *
     * @param int $userId ID uživatele
     * @param string $resource Zdroj
     * @param string $action Akce
     * @return bool
     */
    public function isAllowed(int $userId, string $resource, string $action): bool;

    /**
     * Kontroluje, zda má uživatel roli
     *
     * @param int $userId ID uživatele
     * @param string $roleCode Kód role
     * @return bool
     */
    public function hasRole(int $userId, string $roleCode): bool;

    /**
     * Kontroluje, zda má uživatel alespoň jednu roli z poskytnutého seznamu
     *
     * @param int $userId ID uživatele
     * @param array $roleCodes Pole kódů rolí
     * @return bool
     */
    public function hasAnyRole(int $userId, array $roleCodes): bool;

    /**
     * Kontroluje, zda má uživatel všechny role z poskytnutého seznamu
     *
     * @param int $userId ID uživatele
     * @param array $roleCodes Pole kódů rolí
     * @return bool
     */
    public function hasAllRoles(int $userId, array $roleCodes): bool;

    /**
     * Získá všechna oprávnění uživatele
     *
     * @param int $userId ID uživatele
     * @return array Oprávnění uživatele
     */
    public function getUserPermissions(int $userId): array;
}