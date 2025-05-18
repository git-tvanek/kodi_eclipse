<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Factory\UserFactory;
use App\Service\Interface;
use Nette\Utils\Random;

/**
 * Implementace služby pro uživatele
 *
 * @extends BaseService<User>
 * @implements IUserService
 */
class UserService extends BaseService implements IUserService
{
    /** @var UserRepository */
    private UserRepository $userRepository;
    
    /** @var UserFactory */
    private UserFactory $userFactory;
    
    /**
     * Konstruktor
     *
     * @param UserRepository $userRepository
     * @param UserFactory $userFactory
     */
    public function __construct(
        UserRepository $userRepository,
        UserFactory $userFactory
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->entityClass = User::class;
    }
    
    /**
     * Získá repozitář pro entitu
     *
     * @return UserRepository
     */
    protected function getRepository(): UserRepository
    {
        return $this->userRepository;
    }
    
    /**
     * Registruje nového uživatele
     *
     * @param string $username Uživatelské jméno
     * @param string $email Email
     * @param string $password Heslo
     * @param bool $requireVerification Vyžadovat verifikaci emailu
     * @return int ID vytvořeného uživatele
     */
    public function register(string $username, string $email, string $password, bool $requireVerification = true): int
    {
        // Kontrola, zda uživatelské jméno již existuje
        if ($this->findByUsername($username)) {
            throw new \Exception("Uživatelské jméno '$username' je již používáno.");
        }
        
        // Kontrola, zda email již existuje
        if ($this->findByEmail($email)) {
            throw new \Exception("Email '$email' je již používán.");
        }
        
        $user = $this->userFactory->createFromRegistration($username, $email, $password, $requireVerification);
        return $this->save($user);
    }
    
    /**
     * Ověří přihlašovací údaje uživatele
     *
     * @param string $username Uživatelské jméno nebo email
     * @param string $password Heslo
     * @return User|null Uživatel nebo null při neplatných údajích
     */
    public function authenticate(string $username, string $password): ?User
    {
        // Zkusíme najít uživatele podle uživatelského jména
        $user = $this->findByUsername($username);
        
        // Pokud není nalezen, zkusíme podle emailu
        if (!$user) {
            $user = $this->findByEmail($username);
        }
        
        // Pokud uživatel neexistuje nebo není aktivní, vrátíme null
        if (!$user || !$user->is_active) {
            return null;
        }
        
        // Ověříme heslo
        if (!$user->verifyPassword($password)) {
            return null;
        }
        
        // Aktualizujeme poslední přihlášení
        $this->updateLastLogin($user->id);
        
        return $user;
    }
    
    /**
     * Aktualizuje uživatelské údaje
     *
     * @param int $id ID uživatele
     * @param array $data Data k aktualizaci
     * @return int ID aktualizovaného uživatele
     */
    public function update(int $id, array $data): int
    {
        $user = $this->findById($id);
        
        if (!$user) {
            throw new \Exception("Uživatel s ID $id nebyl nalezen.");
        }
        
        $updatedUser = $this->userFactory->createFromExisting($user, $data);
        return $this->save($updatedUser);
    }
    
    /**
     * Změní heslo uživatele
     *
     * @param int $id ID uživatele
     * @param string $newPassword Nové heslo
     * @return bool Úspěch
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }
        
        $updatedUser = $this->userFactory->createFromExisting($user, [
            'password' => $newPassword
        ]);
        
        return $this->save($updatedUser) > 0;
    }
    
    /**
     * Vygeneruje token pro reset hesla
     *
     * @param string $email Email uživatele
     * @return string|null Token nebo null při nenalezení uživatele
     */
    public function generatePasswordResetToken(string $email): ?string
    {
        $user = $this->findByEmail($email);
        
        if (!$user || !$user->is_active) {
            return null;
        }
        
        // Vytvoření tokenu a nastavení expirace (24 hodin)
        $token = Random::generate(32);
        $expires = new \DateTime('+24 hours');
        
        $updatedUser = $this->userFactory->createFromExisting($user, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);
        
        $this->save($updatedUser);
        
        return $token;
    }
    
    /**
     * Resetuje heslo pomocí tokenu
     *
     * @param string $token Token pro reset
     * @param string $newPassword Nové heslo
     * @return bool Úspěch
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->userRepository->findByPasswordResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $updatedUser = $this->userFactory->createFromExisting($user, [
            'password' => $newPassword,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
        
        return $this->save($updatedUser) > 0;
    }
    
    /**
     * Verifikuje email uživatele pomocí tokenu
     *
     * @param string $token Verifikační token
     * @return bool Úspěch
     */
    public function verifyEmail(string $token): bool
    {
        $user = $this->userRepository->findByVerificationToken($token);
        
        if (!$user) {
            return false;
        }
        
        $updatedUser = $this->userFactory->createFromExisting($user, [
            'is_verified' => true,
            'verification_token' => null
        ]);
        
        return $this->save($updatedUser) > 0;
    }
    
    /**
     * Najde uživatele podle uživatelského jména
     *
     * @param string $username Uživatelské jméno
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }
    
    /**
     * Najde uživatele podle emailu
     *
     * @param string $email Email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }
    
    /**
     * Získá uživatele s jeho rolemi
     *
     * @param int $id ID uživatele
     * @return array|null
     */
    public function getUserWithRoles(int $id): ?array
    {
        return $this->userRepository->getUserWithRoles($id);
    }
    
    /**
     * Přidá uživateli roli
     *
     * @param int $userId ID uživatele
     * @param int $roleId ID role
     * @return bool Úspěch
     */
    public function addRole(int $userId, int $roleId): bool
    {
        return $this->userRepository->addUserRole($userId, $roleId);
    }
    
    /**
     * Odebere uživateli roli
     *
     * @param int $userId ID uživatele
     * @param int $roleId ID role
     * @return bool Úspěch
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        return $this->userRepository->removeUserRole($userId, $roleId);
    }
    
    /**
     * Aktualizuje datum posledního přihlášení
     *
     * @param int $userId ID uživatele
     * @return bool Úspěch
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->userRepository->updateLastLogin($userId);
    }
}