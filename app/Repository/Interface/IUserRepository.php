<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\User;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;

/**
 * Rozhraní pro repozitář uživatelů
 * 
 * @extends IBaseRepository<User>
 */
interface IUserRepository extends IBaseRepository
{
    /**
     * Najde uživatele podle uživatelského jména
     * 
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User;
    
    /**
     * Najde uživatele podle e-mailu
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;
    
    /**
     * Najde uživatele podle tokenu pro reset hesla
     * 
     * @param string $token
     * @return User|null
     */
    public function findByPasswordResetToken(string $token): ?User;
    
    /**
     * Najde uživatele podle tokenu pro verifikaci
     * 
     * @param string $token
     * @return User|null
     */
    public function findByVerificationToken(string $token): ?User;
    
    /**
     * Najde uživatele s jejich rolemi
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUserWithRoles(int $userId): ?array;
    
    /**
     * Najde uživatele s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<User>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'username', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection;
    
    /**
     * Přidá uživateli roli
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function addUserRole(int $userId, int $roleId): bool;
    
    /**
     * Odebere uživateli roli
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function removeUserRole(int $userId, int $roleId): bool;
    
    /**
     * Získá statistiky uživatelských účtů
     * 
     * @return array
     */
    public function getUserStatistics(): array;

    /**
     * Vytvoří nového uživatele
     * 
     * @param User $user
     * @return int
     */
    public function create(User $user): int;
    
    /**
     * Aktualizuje uživatele
     * 
     * @param User $user
     * @return int
     */
    public function update(User $user): int;
    
    /**
     * Aktualizuje uživatelský profil
     * 
     * @param int $userId
     * @param array $profileData
     * @return bool
     */
    public function updateProfile(int $userId, array $profileData): bool;
    
    /**
     * Změní heslo uživatele
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool;
    
    /**
     * Verifikuje emailovou adresu
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail(string $token): bool;
    
    /**
     * Vytvoří a uloží token pro reset hesla
     * 
     * @param int $userId
     * @param string $token
     * @param \DateTime $expires
     * @return bool
     */
    public function createPasswordResetToken(int $userId, string $token, \DateTime $expires): bool;
    
    /**
     * Resetuje heslo pomocí tokenu
     * 
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool;
    
    /**
     * Aktivuje nebo deaktivuje uživatelský účet
     * 
     * @param int $userId
     * @param bool $active
     * @return bool
     */
    public function setActive(int $userId, bool $active): bool;
    
    /**
     * Najde uživatele podle identifikátoru (username nebo email)
     * 
     * @param string $identifier
     * @return User|null
     */
    public function findByIdentifier(string $identifier): ?User;
    
    /**
     * Vrátí všechny uživatele s danou rolí
     * 
     * @param string $roleCode
     * @return Collection<User>
     */
    public function findByRole(string $roleCode): Collection;
    
    /**
     * Vyhledá uživatele podle komplexních kritérií
     * 
     * @param array $criteria
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<User>
     */
    public function search(
        array $criteria, 
        string $sortBy = 'username', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection;
}