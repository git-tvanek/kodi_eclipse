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

    
    /**
     * 🏆 Nejaktivnější uživatelé
     */
    public function getMostActive(int $limit = 10): self
    {
        return $this->sort(function(User $a, User $b) {
                return $b->getReviews()->count() <=> $a->getReviews()->count();
            })
            ->take($limit);
    }

    /**
     * 🆕 Noví uživatelé
     */
    public function getNewUsers(int $days = 30): self
    {
        $since = new \DateTime("-{$days} days");
        return $this->filter(function(User $user) use ($since) {
            return $user->getCreatedAt() >= $since;
        })->sortByCreatedAt('DESC');
    }

    /**
     * 🔥 Nedávno aktivní uživatelé
     */
    public function getRecentlyActive(int $days = 7): self
    {
        $since = new \DateTime("-{$days} days");
        return $this->filter(function(User $user) use ($since) {
            return $user->getLastLogin() && $user->getLastLogin() >= $since;
        })->sortBy('last_login', 'DESC');
    }

    /**
     * ⭐ Top reviewers
     */
    public function getTopReviewers(int $minReviews = 5, int $limit = 10): self
    {
        return $this->filter(function(User $user) use ($minReviews) {
                return $user->getReviews()->count() >= $minReviews;
            })
            ->sort(function(User $a, User $b) {
                $scoreA = $this->calculateReviewerScore($a);
                $scoreB = $this->calculateReviewerScore($b);
                return $scoreB <=> $scoreA;
            })
            ->take($limit);
    }

    /**
     * 🔍 Vyhledávání podle přihlašovacích údajů
     */
    public function searchByCredentials(string $query): self
    {
        if (empty(trim($query))) {
            return $this;
        }
        
        return $this->filter(function(User $user) use ($query) {
            $searchableText = strtolower($user->getUsername() . ' ' . $user->getEmail());
            return str_contains($searchableText, strtolower(trim($query)));
        });
    }

    /**
     * 📊 Uživatelé se statistikami
     */
    public function withDetailedStats(): array
    {
        return $this->map(function(User $user) {
            return [
                'user' => $user,
                'stats' => [
                    'review_count' => $user->getReviews()->count(),
                    'account_age_days' => $this->calculateAccountAge($user),
                    'last_activity' => $user->getLastLogin(),
                    'is_active_reviewer' => $user->getReviews()->count() >= 5,
                    'reviewer_score' => $this->calculateReviewerScore($user),
                    'roles' => $user->getRoles()
                ]
            ];
        });
    }

    /**
     * 📈 Registrační trend
     */
    public function getRegistrationTrend(int $months = 12): array
    {
        $trends = [];
        $now = new \DateTime();
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = clone $now;
            $date->modify("-{$i} months");
            $monthKey = $date->format('Y-m');
            
            $monthUsers = $this->filter(function(User $user) use ($date) {
                return $user->getCreatedAt()->format('Y-m') === $date->format('Y-m');
            });
            
            $trends[] = [
                'month' => $monthKey,
                'new_users' => $monthUsers->count(),
                'verified_users' => $monthUsers->filterVerified()->count(),
                'active_users' => $monthUsers->filterActive()->count()
            ];
        }
        
        return $trends;
    }

    // ========== POMOCNÉ METODY ==========

    private function calculateReviewerScore(User $user): float
    {
        $reviewCount = $user->getReviews()->count();
        $accountAge = $this->calculateAccountAge($user);
        
        // Skóre: počet recenzí * 10 + bonus za délku účtu
        return ($reviewCount * 10) + min($accountAge / 30, 10); // Max 10 bodů za stáří
    }

    private function calculateAccountAge(User $user): int
    {
        $now = new \DateTime();
        $diff = $now->diff($user->getCreatedAt());
        return $diff->days;
    }
}