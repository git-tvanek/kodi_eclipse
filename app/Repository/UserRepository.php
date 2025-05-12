<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use App\Model\Role;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IUserRepository;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @extends BaseRepository<User>
 * @implements IUserRepository
 */
class UserRepository extends BaseRepository implements IUserRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'users';
        $this->entityClass = User::class;
    }

    /**
     * Najde uživatele podle uživatelského jména
     * 
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        /** @var User|null */
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
        /** @var User|null */
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
        // Kontroluje i expiraci tokenu
        $now = new \DateTime();
        
        /** @var User|null */
        $user = $this->findOneBy([
            'password_reset_token' => $token,
            'password_reset_expires >= ?' => $now->format('Y-m-d H:i:s')
        ]);
        
        return $user;
    }
    
    /**
     * Najde uživatele podle tokenu pro verifikaci
     * 
     * @param string $token
     * @return User|null
     */
    public function findByVerificationToken(string $token): ?User
    {
        /** @var User|null */
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
        $user = $this->findById($userId);
        
        if (!$user) {
            return null;
        }
        
        // Získání rolí pro uživatele
        $roles = [];
        $roleRows = $this->database->table('roles')
            ->select('roles.*')
            ->joinWhere('user_roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId);
        
        foreach ($roleRows as $row) {
            $roles[] = Role::fromArray($row->toArray());
        }
        
        return [
            'user' => $user,
            'roles' => new Collection($roles)
        ];
    }
    
    /**
     * Najde uživatele s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<User>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'username', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $selection = $this->getTable();
        
        // Aplikace filtrů
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'username':
                case 'email':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'is_active':
                case 'is_verified':
                    $selection->where($key, (bool) $value);
                    break;
                    
                case 'role_id':
                    $selection->where('id IN ?', 
                        $this->database->table('user_roles')
                            ->where('role_id', $value)
                            ->select('user_id')
                    );
                    break;
                    
                case 'created_after':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at >= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                    
                case 'created_before':
                    if ($value instanceof \DateTime) {
                        $selection->where('created_at <= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                    
                case 'last_login_after':
                    if ($value instanceof \DateTime) {
                        $selection->where('last_login >= ?', $value->format('Y-m-d H:i:s'));
                    }
                    break;
                
                default:
                    if (property_exists('App\Model\User', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Aplikace řazení
        if (property_exists('App\Model\User', $sortBy)) {
            $selection->order("{$sortBy} {$sortDir}");
        } else {
            $selection->order("username ASC"); // Výchozí řazení
        }
        
        // Aplikace stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Konverze na entity
        $items = [];
        foreach ($selection as $row) {
            $items[] = User::fromArray($row->toArray());
        }
        
        return new PaginatedCollection(
            new Collection($items),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
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
        // Kontrola, zda vazba již existuje
        $exists = $this->database->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->count() > 0;
        
        if ($exists) {
            return true; // Vazba už existuje
        }
        
        // Vložení nové vazby
        $result = $this->database->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId
        ]);
        
        return $result !== false;
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
        $result = $this->database->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
        
        return $result > 0;
    }
    
    /**
     * Získá statistiky uživatelských účtů
     * 
     * @return array
     */
    public function getUserStatistics(): array
    {
        // Celkový počet uživatelů
        $totalUsers = $this->count();
        
        // Počet aktivních uživatelů
        $activeUsers = $this->count(['is_active' => true]);
        
        // Počet verifikovaných uživatelů
        $verifiedUsers = $this->count(['is_verified' => true]);
        
        // Počet nových uživatelů za poslední měsíc
        $lastMonth = new \DateTime('-1 month');
        $newUsers = $this->count(['created_at >= ?' => $lastMonth->format('Y-m-d H:i:s')]);
        
        // Počet přihlášení za poslední měsíc
        $loginsLastMonth = $this->database->table($this->tableName)
            ->where('last_login >= ?', $lastMonth->format('Y-m-d H:i:s'))
            ->count();
        
        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'new_users_last_month' => $newUsers,
            'logins_last_month' => $loginsLastMonth
        ];
    }
    
    /**
     * Aktualizuje datum posledního přihlášení uživatele
     * 
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin(int $userId): bool
    {
        $now = new \DateTime();
        
        $result = $this->database->table($this->tableName)
            ->wherePrimary($userId)
            ->update([
                'last_login' => $now->format('Y-m-d H:i:s')
            ]);
        
        return $result > 0;
    }
}