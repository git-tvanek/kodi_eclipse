<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Role;
use App\Entity\User;
use App\Factory\Interface\IUserFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Továrna pro vytváření instancí třídy User
 * 
 * @extends BaseFactory<User>
 * @implements IUserFactory<User>
 */
class UserFactory extends BaseFactory implements IUserFactory
{
    /**
     * Konstruktor
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, User::class);
    }
    
    /**
     * {@inheritDoc}
     */
    public function create(array $data): User
    {
        /** @var User $user */
        $user = $this->createNewInstance();
        return $this->createFromExisting($user, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function createFromExisting($entity, array $data, bool $isNew = true): User
    {
        if (isset($data['username'])) {
            $entity->setUsername($data['username']);
        }
        
        if (isset($data['email'])) {
            $entity->setEmail($data['email']);
        }
        
        if (isset($data['password'])) {
            // Heslo je automaticky zahashováno před uložením
            $entity->setPasswordHash(User::hashPassword($data['password']));
        } elseif (isset($data['password_hash'])) {
            // Pokud je již heslo zahashováno (např. při importu), použijeme přímo hash
            $entity->setPasswordHash($data['password_hash']);
        }
        
        if (isset($data['is_active'])) {
            $entity->setIsActive((bool)$data['is_active']);
        }
        
        if (isset($data['is_verified'])) {
            $entity->setIsVerified((bool)$data['is_verified']);
        }
        
        if (isset($data['verification_token'])) {
            $entity->setVerificationToken($data['verification_token']);
        }
        
        if (isset($data['password_reset_token'])) {
            $entity->setPasswordResetToken($data['password_reset_token']);
        }
        
        if (isset($data['password_reset_expires'])) {
            if ($data['password_reset_expires'] instanceof DateTime) {
                $entity->setPasswordResetExpires($data['password_reset_expires']);
            } elseif (is_string($data['password_reset_expires'])) {
                $entity->setPasswordResetExpires(new DateTime($data['password_reset_expires']));
            }
        }
        
        if (isset($data['profile_image'])) {
            $entity->setProfileImage($data['profile_image']);
        }
        
        if (isset($data['created_at'])) {
            $createdAt = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
            $entity->setCreatedAt($createdAt);
        } elseif ($isNew) {
            $entity->setCreatedAt(new DateTime());
        }
        
        if (isset($data['updated_at'])) {
            $updatedAt = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
            $entity->setUpdatedAt($updatedAt);
        } else {
            $entity->setUpdatedAt(new DateTime());
        }
        
        if (isset($data['last_login'])) {
            if ($data['last_login'] instanceof DateTime) {
                $entity->setLastLogin($data['last_login']);
            } elseif (is_string($data['last_login'])) {
                $entity->setLastLogin(new DateTime($data['last_login']));
            }
        }
        
        // Zpracování rolí
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $role) {
                if ($role instanceof Role) {
                    $entity->addRole($role);
                } elseif (is_array($role) && isset($role['id'])) {
                    /** @var Role $roleEntity */
                    $roleEntity = $this->getReference(Role::class, (int)$role['id']);
                    $entity->addRole($roleEntity);
                } elseif (is_string($role)) {
                    // Pokud je role zadána jako string (kód role), vyhledáme ji v DB
                    $roleRepository = $this->entityManager->getRepository(Role::class);
                    /** @var Role|null $roleEntity */
                    $roleEntity = $roleRepository->findOneBy(['code' => $role]);
                    if ($roleEntity) {
                        $entity->addRole($roleEntity);
                    }
                }
            }
        }
        
        return $entity;
    }
}