<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Factory\Interface\IUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use DateTime;

/**
 * Továrna pro vytváření uživatelů
 * 
 * @template-extends SchemaFactory<User>
 * @implements IUserFactory
 */
class UserFactory extends BuilderFactory implements IUserFactory
{
    private Passwords $passwords;
    
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface|null $validator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ?ValidatorInterface $validator = null
    ) {
        parent::__construct($entityManager, $validator);
        $this->passwords = new Passwords();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getEntityClass(): string
    {
        return User::class;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getRequiredFields(): array
    {
        return [
            'username',
            'email'
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getDefaultValues(): array
    {
        return [
            'is_active' => true,
            'is_verified' => false,
            'verification_token' => null,
            'password_reset_token' => null,
            'password_reset_expires' => null,
            'profile_image' => null,
            'last_login' => null
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateSchema(array $data): array
    {
        // Validace unikátnosti uživatelského jména
        if (isset($data['username'])) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $data['username']]);
            
            if ($existingUser && (!isset($data['id']) || $existingUser->getId() !== $data['id'])) {
                throw new \InvalidArgumentException("User with username '{$data['username']}' already exists");
            }
        }
        
        // Validace unikátnosti emailu
        if (isset($data['email'])) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            
            if ($existingUser && (!isset($data['id']) || $existingUser->getId() !== $data['id'])) {
                throw new \InvalidArgumentException("User with email '{$data['email']}' already exists");
            }
        }
        
        return $data;
    }
    
    /**
     * {@inheritdoc}
     */
    public function create(array $data): User
    {
        // Pokud je uvedeno heslo, převést na hash
        if (isset($data['password']) && !isset($data['password_hash'])) {
            $data['password_hash'] = $this->passwords->hash($data['password']);
            unset($data['password']);
        }
        
        return parent::create($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(User $user, array $data, bool $updateTimestamp = true): User
    {
        $overrideData = $data;
        
        // Pokud je uvedeno heslo, převést na hash
        if (isset($overrideData['password'])) {
            $overrideData['password_hash'] = $this->passwords->hash($overrideData['password']);
            unset($overrideData['password']);
        }
        
        $result = parent::createFromExisting($user, $overrideData, false);
        
        // Volitelná aktualizace timestamp
        if ($updateTimestamp) {
            $this->updateTimestamps($result, false);
        }
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromRegistration(string $username, string $email, string $password, bool $requireVerification = true): User
    {
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
            'is_verified' => !$requireVerification
        ];
        
        if ($requireVerification) {
            $data['verification_token'] = Random::generate(32);
        }
        
        return $this->create($data);
    }

    
}