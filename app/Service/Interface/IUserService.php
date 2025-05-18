<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní služby pro uživatele
 *
 * @extends IBaseService<User>
 */
interface IUserService extends IBaseService
{
    /**
     * Registruje nového uživatele
     *
     * @param string $username Uživatelské jméno
     * @param string $email Email
     * @param string $password Heslo
     * @param bool $requireVerification Vyžadovat verifikaci emailu
     * @return int ID vytvořeného uživatele
     */
    public function register(string $username, string $email, string $password, bool $requireVerification = true): int;

    /**
     * Ověří přihlašovací údaje uživatele
     *
     * @param string $username Uživatelské jméno nebo email
     * @param string $password Heslo
     * @return User|null Uživatel nebo null při neplatných údajích
     */
    public function authenticate(string $username, string $password): ?User;

    /**
     * Aktualizuje uživatelské údaje
     *
     * @param int $id ID uživatele
     * @param array $data Data k aktualizaci
     * @return int ID aktualizovaného uživatele
     */
    public function update(int $id, array $data): int;

    /**
     * Změní heslo uživatele
     *
     * @param int $id ID uživatele
     * @param string $newPassword Nové heslo
     * @return bool Úspěch
     */
    public function changePassword(int $id, string $newPassword): bool;

    /**
     * Vygeneruje token pro reset hesla
     *
     * @param string $email Email uživatele
     * @return string|null Token nebo null při nenalezení uživatele
     */
    public function generatePasswordResetToken(string $email): ?string;

    /**
     * Resetuje heslo pomocí tokenu
     *
     * @param string $token Token pro reset
     * @param string $newPassword Nové heslo
     * @return bool Úspěch
     */
    public function resetPassword(string $token, string $newPassword): bool;

    /**
     * Verifikuje email uživatele pomocí tokenu
     *
     * @param string $token Verifikační token
     * @return bool Úspěch
     */
    public function verifyEmail(string $token): bool;

    /**
     * Najde uživatele podle uživatelského jména
     *
     * @param string $username Uživatelské jméno
     * @return User|null
     */
    public function findByUsername(string $username): ?User;

    /**
     * Najde uživatele podle emailu
     *
     * @param string $email Email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Získá uživatele s jeho rolemi
     *
     * @param int $id ID uživatele
     * @return array|null
     */
    public function getUserWithRoles(int $id): ?array;

    /**
     * Přidá uživateli roli
     *
     * @param int $userId ID uživatele
     * @param int $roleId ID role
     * @return bool Úspěch
     */
    public function addRole(int $userId, int $roleId): bool;

    /**
     * Odebere uživateli roli
     *
     * @param int $userId ID uživatele
     * @param int $roleId ID role
     * @return bool Úspěch
     */
    public function removeRole(int $userId, int $roleId): bool;

    /**
     * Aktualizuje datum posledního přihlášení
     *
     * @param int $userId ID uživatele
     * @return bool Úspěch
     */
    public function updateLastLogin(int $userId): bool;
}