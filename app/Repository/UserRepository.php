<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\Passwords;

/**
 * Repozitář pro práci s uživateli
 * 
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository implements IUserRepository
{
    protected string $defaultAlias = 'u';
    private Passwords $passwords;
    
    /**
     * Konstruktor
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, User::class);
        $this->passwords = new Passwords();
    }
    
    /**
     * Vytvoří typovanou kolekci uživatelů
     * 
     * @param array<User> $entities
     * @return Collection<User>
     */
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    /**
     * Najde uživatele podle uživatelského jména
     * 
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }
    
    /**
     * Najde uživatele podle e-mailu
     * 
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    
    /**
     * Najde uživatele podle tokenu pro reset hesla
     * 
     * @param string $token
     * @return User|null
     */
    public function findByPasswordResetToken(string $token): ?User
    {
        $now = new \DateTime();
        
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->where("$this->defaultAlias.password_reset_token = :token")
            ->andWhere("$this->defaultAlias.password_reset_expires >= :now")
            ->setParameter('token', $token)
            ->setParameter('now', $now);
            
        return $qb->getQuery()->getOneOrNullResult();
    }
    
    /**
     * Najde uživatele podle tokenu pro verifikaci
     * 
     * @param string $token
     * @return User|null
     */
    public function findByVerificationToken(string $token): ?User
    {
        return $this->findOneBy(['verification_token' => $token]);
    }
    
    /**
     * Najde uživatele s jejich rolemi
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUserWithRoles(int $userId): ?array
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return null;
        }
        
        // Role are already loaded through association
        $roles = $user->getRoleEntities()->toArray();
        
        return [
            'user' => $user,
            'roles' => $this->createCollection($roles)
        ];
    }
    
    /**
     * Vyhledá uživatele podle zadaných filtrů
     * 
     * @param array $filters Pole filtrů
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<User>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'username', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias);
        
        // Apply standard filters
        $qb = $this->applyFilters($qb, $filters, $this->defaultAlias);
        
        // Custom filters specific to users
        if (isset($filters['role_id']) && $filters['role_id'] !== null) {
            $qb->join("$this->defaultAlias.roles", 'r')
               ->andWhere('r.id = :roleId')
               ->setParameter('roleId', $filters['role_id']);
        }
        
        // Apply sorting
        if ($this->hasProperty($sortBy)) {
            $qb->orderBy("$this->defaultAlias.$sortBy", $sortDir);
        } else {
            $qb->orderBy("$this->defaultAlias.username", 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    /**
     * Přidá uživateli roli
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function addUserRole(int $userId, int $roleId): bool
    {
        $user = $this->find($userId);
        $role = $this->entityManager->getReference(Role::class, $roleId);
        
        if (!$user || !$role) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $role) {
            if ($user->getRoleEntities()->contains($role)) {
                return true; // Already has this role
            }
            
            $user->addRole($role);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Odebere uživateli roli
     * 
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function removeUserRole(int $userId, int $roleId): bool
    {
        $user = $this->find($userId);
        $role = $this->entityManager->getReference(Role::class, $roleId);
        
        if (!$user || !$role) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $role) {
            if (!$user->getRoleEntities()->contains($role)) {
                return true; // Doesn't have this role anyway
            }
            
            $user->removeRole($role);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Získá statistiky uživatelských účtů
     * 
     * @return array
     */
    public function getUserStatistics(): array
    {
        // Total user count
        $totalUsers = $this->count([]);
        
        // Active users count
        $activeUsers = $this->count(['is_active' => true]);
        
        // Verified users count
        $verifiedUsers = $this->count(['is_verified' => true]);
        
        // New users in the last month
        $lastMonth = new \DateTime('-1 month');
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->select("COUNT($this->defaultAlias.id)")
            ->where("$this->defaultAlias.created_at >= :lastMonth")
            ->setParameter('lastMonth', $lastMonth);
        $newUsers = (int)$qb->getQuery()->getSingleScalarResult();
        
        // Logins in the last month
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->select("COUNT($this->defaultAlias.id)")
            ->where("$this->defaultAlias.last_login >= :lastMonth")
            ->setParameter('lastMonth', $lastMonth);
        $loginsLastMonth = (int)$qb->getQuery()->getSingleScalarResult();
        
        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'new_users_last_month' => $newUsers,
            'logins_last_month' => $loginsLastMonth
        ];
    }

    /**
     * Vytvoří nového uživatele
     * 
     * @param User $user
     * @return int ID vytvořeného uživatele
     */
    public function create(User $user): int
    {
        return $this->save($user);
    }
    
    /**
     * Aktualizuje uživatele
     * 
     * @param User $user
     * @return int ID aktualizovaného uživatele
     */
    public function update(User $user): int
    {
        return $this->updateEntity($user);
    }
    
    /**
     * Aktualizuje uživatelský profil
     * 
     * @param int $userId
     * @param array $profileData
     * @return bool
     */
    public function updateProfile(int $userId, array $profileData): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $profileData) {
            $modified = false;
            
            if (isset($profileData['username'])) {
                $user->setUsername($profileData['username']);
                $modified = true;
            }
            
            if (isset($profileData['email'])) {
                $user->setEmail($profileData['email']);
                $modified = true;
            }
            
            if (isset($profileData['profile_image'])) {
                $user->setProfileImage($profileData['profile_image']);
                $modified = true;
            }
            
            if ($modified) {
                $this->updateTimestamps($user, false);
                $this->entityManager->flush();
            }
            
            return $modified;
        });
    }
    
    /**
     * Změní heslo uživatele
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $newPassword) {
            $user->setPasswordHash($this->passwords->hash($newPassword));
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Verifikuje emailovou adresu
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail(string $token): bool
    {
        $user = $this->findByVerificationToken($token);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user) {
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Vytvoří a uloží token pro reset hesla
     * 
     * @param int $userId
     * @param string $token
     * @param \DateTime $expires
     * @return bool
     */
    public function createPasswordResetToken(int $userId, string $token, \DateTime $expires): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $token, $expires) {
            $user->setPasswordResetToken($token);
            $user->setPasswordResetExpires($expires);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Resetuje heslo pomocí tokenu
     * 
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->findByPasswordResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $newPassword) {
            $user->setPasswordHash($this->passwords->hash($newPassword));
            $user->setPasswordResetToken(null);
            $user->setPasswordResetExpires(null);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Aktivuje nebo deaktivuje uživatelský účet
     * 
     * @param int $userId
     * @param bool $active
     * @return bool
     */
    public function setActive(int $userId, bool $active): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        return $this->transaction(function() use ($user, $active) {
            $user->setIsActive($active);
            $this->updateTimestamps($user, false);
            $this->entityManager->flush();
            
            return true;
        });
    }
    
    /**
     * Najde uživatele podle identifikátoru (username nebo email)
     * 
     * @param string $identifier
     * @return User|null
     */
    public function findByIdentifier(string $identifier): ?User
    {
        $user = $this->findByUsername($identifier);
        
        if (!$user) {
            $user = $this->findByEmail($identifier);
        }
        
        return $user;
    }
    
    /**
     * Vrátí všechny uživatele s danou rolí
     * 
     * @param string $roleCode
     * @return Collection<User>
     */
    public function findByRole(string $roleCode): Collection
    {
        $qb = $this->createQueryBuilder($this->defaultAlias)
            ->join("$this->defaultAlias.roles", 'r')
            ->where('r.code = :roleCode')
            ->setParameter('roleCode', $roleCode);
            
        $users = $qb->getQuery()->getResult();
        
        return $this->createCollection($users);
    }
    
    /**
     * Vyhledá uživatele podle komplexních kritérií
     * 
     * @param array $criteria
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<User>
     */
    public function search(
        array $criteria, 
        string $sortBy = 'username', 
        string $sortDir = 'ASC', 
        int $page = 1, 
        int $itemsPerPage = 10
    ): PaginatedCollection
    {
        return $this->findWithFilters($criteria, $sortBy, $sortDir, $page, $itemsPerPage);
    }
}