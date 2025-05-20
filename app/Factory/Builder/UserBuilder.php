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
     * @return self
     */
    public function asAdmin(string $username, string $email, string $password): self
    {
        $this->setValue('username', $username);
        $this->setValue('email', $email);
        $this->setValue('password', $password);
        $this->setValue('is_active', true);
        $this->setValue('is_verified', true);
        
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