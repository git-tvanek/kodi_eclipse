<?php
namespace App\Collection;

use App\Entity\User;

/**
 * Typovaná kolekce pro uživatele
 * 
 * @extends Collection<User>
 */
class UserCollection extends Collection
{
    /**
     * Seřadí podle uživatelského jména
     */
    public function sortByUsername(string $direction = 'ASC'): self
    {
        return $this->sort(function(User $a, User $b) use ($direction) {
            return $direction === 'ASC' 
                ? strcmp($a->getUsername(), $b->getUsername())
                : strcmp($b->getUsername(), $a->getUsername());
        });
    }
    
    /**
     * Seřadí podle data registrace
     */
    public function sortByCreatedAt(string $direction = 'DESC'): self
    {
        return $this->sort(function(User $a, User $b) use ($direction) {
            return $direction === 'DESC' 
                ? $b->getCreatedAt() <=> $a->getCreatedAt()
                : $a->getCreatedAt() <=> $b->getCreatedAt();
        });
    }
    
    /**
     * Filtruje aktivní uživatele
     */
    public function filterActive(): self
    {
        return $this->filter(function(User $user): bool {
            return $user->isActive();
        });
    }
    
    /**
     * Filtruje ověřené uživatele
     */
    public function filterVerified(): self
    {
        return $this->filter(function(User $user): bool {
            return $user->isVerified();
        });
    }
    
    /**
     * Filtruje podle role
     */
    public function filterByRole(string $roleCode): self
    {
        return $this->filter(function(User $user) use ($roleCode): bool {
            foreach ($user->getRoleEntities() as $role) {
                if ($role->getCode() === $roleCode) {
                    return true;
                }
            }
            return false;
        });
    }
    
    /**
     * Najde uživatele podle emailu
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findFirst(function(User $user) use ($email): bool {
            return $user->getEmail() === $email;
        });
    }
    
    /**
     * Najde uživatele podle uživatelského jména
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findFirst(function(User $user) use ($username): bool {
            return $user->getUsername() === $username;
        });
    }
    
    /**
     * Získá uživatelská jména jako pole
     */
    public function getUsernames(): array
    {
        return $this->map(function(User $user): string {
            return $user->getUsername();
        });
    }
    
    /**
     * Získá emaily jako pole
     */
    public function getEmails(): array
    {
        return $this->map(function(User $user): string {
            return $user->getEmail();
        });
    }
    
    /**
     * Seskupí podle rolí
     */
    public function groupByRole(): array
    {
        $groups = [];
        
        foreach ($this as $user) {
            foreach ($user->getRoleEntities() as $role) {
                $roleCode = $role->getCode();
                if (!isset($groups[$roleCode])) {
                    $groups[$roleCode] = [];
                }
                $groups[$roleCode][] = $user;
            }
        }
        
        // Převedeme na Collection instances
        return array_map(fn($users) => new static($users), $groups);
    }
    
    /**
     * Získá statistiky uživatelů
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->count(),
            'active' => $this->filterActive()->count(),
            'verified' => $this->filterVerified()->count(),
            'inactive' => $this->count() - $this->filterActive()->count(),
            'unverified' => $this->count() - $this->filterVerified()->count()
        ];
    }
}