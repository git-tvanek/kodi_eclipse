<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\User;
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
}