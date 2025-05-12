<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Nette\SmartObject;
use Nette\Security\Passwords;

class User
{
    use SmartObject;

    public int $id;
    public string $username;
    public string $email;
    public string $password_hash;
    public bool $is_active = true;
    public bool $is_verified = false;
    public ?string $verification_token = null;
    public ?string $password_reset_token = null;
    public ?DateTime $password_reset_expires = null;
    public ?string $profile_image = null;
    public DateTime $created_at;
    public DateTime $updated_at;
    public ?DateTime $last_login = null;

    /**
     * Create a User instance from array data
     */
    public static function fromArray(array $data): self
    {
        $user = new self();
        
        if (isset($data['id'])) {
            $user->id = (int) $data['id'];
        }
        
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->password_hash = $data['password_hash'];
        $user->is_active = (bool) ($data['is_active'] ?? true);
        $user->is_verified = (bool) ($data['is_verified'] ?? false);
        $user->verification_token = $data['verification_token'] ?? null;
        $user->password_reset_token = $data['password_reset_token'] ?? null;
        
        if (isset($data['password_reset_expires'])) {
            $user->password_reset_expires = $data['password_reset_expires'] instanceof DateTime 
                ? $data['password_reset_expires'] 
                : new DateTime($data['password_reset_expires']);
        }
        
        $user->profile_image = $data['profile_image'] ?? null;
        
        if (isset($data['created_at'])) {
            $user->created_at = $data['created_at'] instanceof DateTime 
                ? $data['created_at'] 
                : new DateTime($data['created_at']);
        } else {
            $user->created_at = new DateTime();
        }
        
        if (isset($data['updated_at'])) {
            $user->updated_at = $data['updated_at'] instanceof DateTime 
                ? $data['updated_at'] 
                : new DateTime($data['updated_at']);
        } else {
            $user->updated_at = new DateTime();
        }
        
        if (isset($data['last_login'])) {
            $user->last_login = $data['last_login'] instanceof DateTime 
                ? $data['last_login'] 
                : new DateTime($data['last_login']);
        }
        
        return $user;
    }

    /**
     * Convert the User instance to an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id ?? null,
            'username' => $this->username,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'verification_token' => $this->verification_token,
            'password_reset_token' => $this->password_reset_token,
            'password_reset_expires' => $this->password_reset_expires,
            'profile_image' => $this->profile_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_login' => $this->last_login,
        ];
    }

    /**
     * Verify if a password matches the hash
     */
    public function verifyPassword(string $password): bool
    {
        return (new Passwords())->verify($password, $this->password_hash);
    }

    /**
     * Create a password hash
     */
    public static function hashPassword(string $password): string
    {
        return (new Passwords())->hash($password);
    }
}