<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\Interface\IUserFactory;
use App\Model\User;
use Nette\Utils\Random;

class UserFactory implements IUserFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): User
    {
        return User::fromArray($data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromRegistration(string $username, string $email, string $password, bool $requireVerification = true): User
    {
        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->password_hash = User::hashPassword($password);
        $user->is_active = true;
        $user->is_verified = !$requireVerification;
        
        if ($requireVerification) {
            $user->verification_token = Random::generate(32);
        }
        
        $user->created_at = new \DateTime();
        $user->updated_at = new \DateTime();
        
        return $user;
    }
    
    /**
     * {@inheritdoc}
     */
    public function createFromExisting(User $user, array $data, bool $updateTimestamp = true): User
    {
        $updatedUser = clone $user;
        
        if (isset($data['username'])) {
            $updatedUser->username = $data['username'];
        }
        
        if (isset($data['email'])) {
            $updatedUser->email = $data['email'];
        }
        
        if (isset($data['password'])) {
            $updatedUser->password_hash = User::hashPassword($data['password']);
        }
        
        if (isset($data['is_active'])) {
            $updatedUser->is_active = (bool) $data['is_active'];
        }
        
        if (isset($data['is_verified'])) {
            $updatedUser->is_verified = (bool) $data['is_verified'];
        }
        
        if (isset($data['profile_image'])) {
            $updatedUser->profile_image = $data['profile_image'];
        }
        
        if ($updateTimestamp) {
            $updatedUser->updated_at = new \DateTime();
        }
        
        return $updatedUser;
    }
}