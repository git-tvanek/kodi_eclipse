<?php

declare(strict_types=1);

namespace App\Factory\Interface;

use App\Entity\User;

/**
 * Rozhraní pro továrnu uživatelů
 */
interface IUserFactory
{
    /**
     * Vytvoří instanci uživatele z dat
     * 
     * @param array $data
     * @return User
     */
    public function create(array $data): User;
    
    /**
     * Vytvoří nového uživatele z registračních dat
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @param bool $requireVerification
     * @return User
     */
    public function createFromRegistration(string $username, string $email, string $password, bool $requireVerification = true): User;
    
    /**
     * Vytvoří novou instanci z existující s aktualizovanými daty
     * 
     * @param User $user
     * @param array $data
     * @param bool $updateTimestamp
     * @return User
     */
    public function createFromExisting(User $user, array $data, bool $updateTimestamp = true): User;
}