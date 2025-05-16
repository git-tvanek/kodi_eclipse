<?php

declare(strict_types=1);

namespace App\Repository\Doctrine;

use App\Entity\User;
use App\Entity\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\Passwords;

/**
 * @extends BaseDoctrineRepository<User>
 */
class UserRepository extends BaseDoctrineRepository implements IUserRepository
{
    private Passwords $passwords;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, User::class);
        $this->passwords = new Passwords();
    }
    
    protected function createCollection(array $entities): Collection
    {
        return new Collection($entities);
    }
    
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }
    
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
    
    public function findByPasswordResetToken(string $token): ?User
    {
        $now = new \DateTime();
        
        $qb = $this->createQueryBuilder('u')
            ->where('u.password_reset_token = :token')
            ->andWhere('u.password_reset_expires >= :now')
            ->setParameter('token', $token)
            ->setParameter('now', $now);
            
        return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function findByVerificationToken(string $token): ?User
    {
        return $this->findOneBy(['verification_token' => $token]);
    }
    
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
            'roles' => new Collection($roles)
        ];
    }
    
    public function findWithFilters(array $filters = [], string $sortBy = 'username', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $qb = $this->createQueryBuilder('u');
        
        // Apply filters
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'username':
                case 'email':
                    $qb->andWhere("u.$key LIKE :$key")
                       ->setParameter($key, '%' . $value . '%');
                    break;
                
                case 'is_active':
                case 'is_verified':
                    $qb->andWhere("u.$key = :$key")
                       ->setParameter($key, (bool)$value);
                    break;
                
                case 'role_id':
                    $qb->join('u.roles', 'r')
                       ->andWhere('r.id = :roleId')
                       ->setParameter('roleId', $value);
                    break;
                
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('u.created_at >= :createdAfter')
                           ->setParameter('createdAfter', $value);
                    }
                    break;
                
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('u.created_at <= :createdBefore')
                           ->setParameter('createdBefore', $value);
                    }
                    break;
                
                case 'last_login_after':
                    if ($value instanceof \DateTime) {
                        $qb->andWhere('u.last_login >= :lastLoginAfter')
                           ->setParameter('lastLoginAfter', $value);
                    }
                    break;
                
                default:
                    if (property_exists(User::class, $key)) {
                        $qb->andWhere("u.$key = :$key")
                           ->setParameter($key, $value);
                    }
                    break;
            }
        }
        
        // Apply sorting
        if (property_exists(User::class, $sortBy)) {
            $qb->orderBy("u.$sortBy", $sortDir);
        } else {
            $qb->orderBy('u.username', 'ASC');
        }
        
        return $this->paginate($qb, $page, $itemsPerPage);
    }
    
    public function addUserRole(int $userId, int $roleId): bool
    {
        $user = $this->find($userId);
        $role = $this->entityManager->getReference(Role::class, $roleId);
        
        if (!$user || !$role) {
            return false;
        }
        
        if ($user->getRoleEntities()->contains($role)) {
            return true; // Already has this role
        }
        
        $user->addRole($role);
        $this->entityManager->flush();
        
        return true;
    }
    
    public function removeUserRole(int $userId, int $roleId): bool
    {
        $user = $this->find($userId);
        $role = $this->entityManager->getReference(Role::class, $roleId);
        
        if (!$user || !$role) {
            return false;
        }
        
        if (!$user->getRoleEntities()->contains($role)) {
            return true; // Doesn't have this role anyway
        }
        
        $user->removeRole($role);
        $this->entityManager->flush();
        
        return true;
    }
    
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
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.created_at >= :lastMonth')
            ->setParameter('lastMonth', $lastMonth);
        $newUsers = (int)$qb->getQuery()->getSingleScalarResult();
        
        // Logins in the last month
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.last_login >= :lastMonth')
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

    public function create(User $user): int
    {
        if (empty($user->getCreatedAt())) {
            $user->setCreatedAt(new \DateTime());
        }
        
        if (empty($user->getUpdatedAt())) {
            $user->setUpdatedAt(new \DateTime());
        }
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user->getId();
    }
    
    public function update(User $user): int
    {
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user->getId();
    }
    
    public function updateProfile(int $userId, array $profileData): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
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
            $user->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
        }
        
        return $modified;
    }
    
    public function changePassword(int $userId, string $newPassword): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $user->setPasswordHash($this->passwords->hash($newPassword));
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        return true;
    }
    
    public function verifyEmail(string $token): bool
    {
        $user = $this->findByVerificationToken($token);
        
        if (!$user) {
            return false;
        }
        
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        return true;
    }
    
    public function createPasswordResetToken(int $userId, string $token, \DateTime $expires): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpires($expires);
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        return true;
    }
    
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->findByPasswordResetToken($token);
        
        if (!$user) {
            return false;
        }
        
        $user->setPasswordHash($this->passwords->hash($newPassword));
        $user->setPasswordResetToken(null);
        $user->setPasswordResetExpires(null);
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        return true;
    }
    
    public function setActive(int $userId, bool $active): bool
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $user->setIsActive($active);
        $user->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        return true;
    }
    
    public function findByIdentifier(string $identifier): ?User
    {
        $user = $this->findByUsername($identifier);
        
        if (!$user) {
            $user = $this->findByEmail($identifier);
        }
        
        return $user;
    }
    
    public function findByRole(string $roleCode): Collection
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.roles', 'r')
            ->where('r.code = :roleCode')
            ->setParameter('roleCode', $roleCode);
            
        $users = $qb->getQuery()->getResult();
        
        return new Collection($users);
    }
    
    public function search(array $criteria, string $sortBy = 'username', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        return $this->findWithFilters($criteria, $sortBy, $sortDir, $page, $itemsPerPage);
    }
}