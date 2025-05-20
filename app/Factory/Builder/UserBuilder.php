<?php

declare(strict_types=1);

namespace App\Factory\Builder;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Factory\Interface\IEntityBuilder;
use Nette\Utils\Random;

/**
 * Builder pro vytváření uživatelů
 * 
 * @template-extends EntityBuilder<User, UserFactory>
 * @implements IEntityBuilder<User>
 */
class UserBuilder extends EntityBuilder
{
    /**
     * @param UserFactory $factory
     */
    public function __construct(UserFactory $factory)
    {
        parent::__construct($factory);
    }
    
    /**
     * Nastaví uživatelské jméno
     * 
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self
    {
        return $this->setValue('username', $username);
    }
    
    /**
     * Nastaví e-mail
     * 
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        return $this->setValue('email', $email);
    }
    
    /**
     * Nastaví heslo (pro vytvoření bude automaticky zahašováno)
     * 
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self
    {
        return $this->setValue('password', $password);
    }
    
    /**
     * Nastaví přímo hash hesla
     * 
     * @param string $passwordHash
     * @return self
     */
    public function setPasswordHash(string $passwordHash): self
    {
        return $this->setValue('password_hash', $passwordHash);
    }
    
    /**
     * Nastaví příznak aktivního účtu
     * 
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        return $this->setValue('is_active', $isActive);
    }
    
    /**
     * Nastaví příznak ověřeného účtu
     * 
     * @param bool $isVerified
     * @return self
     */
    public function setIsVerified(bool $isVerified): self
    {
        return $this->setValue('is_verified', $isVerified);
    }
    
    /**
     * Nastaví token pro ověření účtu
     * 
     * @param string|null $token
     * @return self
     */
    public function setVerificationToken(?string $token): self
    {
        return $this->setValue('verification_token', $token);
    }
    
    /**
     * Vygeneruje nový token pro ověření účtu
     * 
     * @return self
     */
    public function generateVerificationToken(): self
    {
        return $this->setValue('verification_token', Random::generate(32));
    }
    
    /**
     * Nastaví token pro reset hesla
     * 
     * @param string|null $token
     * @return self
     */
    public function setPasswordResetToken(?string $token): self
    {
        return $this->setValue('password_reset_token', $token);
    }
    
    /**
     * Nastaví datum expirace tokenu pro reset hesla
     * 
     * @param \DateTime|null $expires
     * @return self
     */
    public function setPasswordResetExpires(?\DateTime $expires): self
    {
        return $this->setValue('password_reset_expires', $expires);
    }
    
    /**
     * Nastaví URL profilového obrázku
     * 
     * @param string|null $profileImage
     * @return self
     */
    public function setProfileImage(?string $profileImage): self
    {
        return $this->setValue('profile_image', $profileImage);
    }
    
    /**
     * Nastaví datum posledního přihlášení
     * 
     * @param \DateTime|null $lastLogin
     * @return self
     */
    public function setLastLogin(?\DateTime $lastLogin): self
    {
        return $this->setValue('last_login', $lastLogin);
    }
    
    /**
     * Přidá ID role, kterou uživatel má mít
     * 
     * @param int $roleId
     * @return self
     */
    public function addRoleId(int $roleId): self
    {
        if (!isset($this->data['role_ids'])) {
            $this->data['role_ids'] = [];
        }
        
        if (!in_array($roleId, $this->data['role_ids'])) {
            $this->data['role_ids'][] = $roleId;
        }
        
        return $this;
    }
    
    /**
     * Odebere ID role, kterou uživatel nemá mít
     * 
     * @param int $roleId
     * @return self
     */
    public function removeRoleId(int $roleId): self
    {
        if (isset($this->data['role_ids'])) {
            $this->data['role_ids'] = array_filter(
                $this->data['role_ids'], 
                function ($id) use ($roleId) {
                    return $id !== $roleId;
                }
            );
        }
        
        return $this;
    }
    
    /**
     * Nastaví ID rolí, které uživatel má mít
     * 
     * @param array<int> $roleIds
     * @return self
     */
    public function setRoleIds(array $roleIds): self
    {
        return $this->setValue('role_ids', $roleIds);
    }
    
    /**
     * Vytvoří účet z registračních údajů
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @param bool $requireVerification
     * @return self
     */
    public function fromRegistration(string $username, string $email, string $password, bool $requireVerification = true): self
    {
        $this->setValue('username', $username);
        $this->setValue('email', $email);
        $this->setValue('password', $password);
        $this->setValue('is_active', true);
        $this->setValue('is_verified', !$requireVerification);
        
        // Výchozí role pro nové uživatele
        $this->setValue('role_ids', []);
        
        if ($requireVerification) {
            $this->setValue('verification_token', Random::generate(32));
        }
        
        return $this;
    }
    
    /**
     * Vytvoří administrátorský účet
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @param int $adminRoleId ID role administrátora
     * @return self
     */
    public function asAdmin(string $username, string $email, string $password, int $adminRoleId): self
    {
        $this->setValue('username', $username);
        $this->setValue('email', $email);
        $this->setValue('password', $password);
        $this->setValue('is_active', true);
        $this->setValue('is_verified', true);
        
        // Přidání administrátorské role
        $this->setValue('role_ids', [$adminRoleId]);
        
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function build(): User
    {
        return $this->factory->createFromBuilder($this->data);
    }
}