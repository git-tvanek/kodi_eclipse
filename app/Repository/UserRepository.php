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
use Nette\Security\Passwords;

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
        $this->passwords = new Passwords();
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

     /**
     * Vytvoří nového uživatele
     * 
     * @param User $user
     * @return int
     */
    public function create(User $user): int
    {
        if (empty($user->created_at)) {
            $user->created_at = new \DateTime();
        }
        
        if (empty($user->updated_at)) {
            $user->updated_at = new \DateTime();
        }
        
        return $this->save($user);
    }
    
    /**
     * Aktualizuje uživatele
     * 
     * @param User $user
     * @return int
     */
    public function update(User $user): int
    {
        $user->updated_at = new \DateTime();
        return $this->save($user);
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
        $user = $this->findById($userId);
        
        if (!$user) {
            return false;
        }
        
        // Filtrování dat - jen povolené položky profilu
        $allowedFields = ['username', 'email', 'profile_image'];
        $filteredData = array_intersect_key($profileData, array_flip($allowedFields));
        
        if (empty($filteredData)) {
            return false;
        }
        
        $filteredData['updated_at'] = new \DateTime();
        
        return $this->getTable()->wherePrimary($userId)->update($filteredData) > 0;
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
        return $this->getTable()->wherePrimary($userId)->update([
            'password_hash' => $this->passwords->hash($newPassword),
            'updated_at' => new \DateTime()
        ]) > 0;
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
        
        return $this->getTable()->wherePrimary($user->id)->update([
            'is_verified' => true,
            'verification_token' => null,
            'updated_at' => new \DateTime()
        ]) > 0;
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
        return $this->getTable()->wherePrimary($userId)->update([
            'password_reset_token' => $token,
            'password_reset_expires' => $expires,
            'updated_at' => new \DateTime()
        ]) > 0;
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
        
        return $this->getTable()->wherePrimary($user->id)->update([
            'password_hash' => $this->passwords->hash($newPassword),
            'password_reset_token' => null,
            'password_reset_expires' => null,
            'updated_at' => new \DateTime()
        ]) > 0;
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
        return $this->getTable()->wherePrimary($userId)->update([
            'is_active' => $active,
            'updated_at' => new \DateTime()
        ]) > 0;
    }
    
    /**
     * Najde uživatele podle identifikátoru (username nebo email)
     * 
     * @param string $identifier
     * @return User|null
     */
    public function findByIdentifier(string $identifier): ?User
    {
        // Zkusíme najít podle username
        $user = $this->findByUsername($identifier);
        
        // Pokud není nalezen, zkusíme podle email
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
        $role = $this->database->table('roles')
            ->where('code', $roleCode)
            ->fetch();
            
        if (!$role) {
            return new Collection();
        }
        
        $userRows = $this->database->table($this->tableName)
            ->select($this->tableName.'.*')
            ->joinWhere('user_roles', 'user_roles.user_id = '.$this->tableName.'.id')
            ->where('user_roles.role_id', $role->id);
            
        $users = [];
        foreach ($userRows as $row) {
            $users[] = User::fromArray($row->toArray());
        }
        
        return new Collection($users);
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
    ): PaginatedCollection {
        $selection = $this->getTable();
        
        // Přidáme jednotlivá kritéria
        foreach ($criteria as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'username':
                case 'email':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'role':
                    $roleId = $this->database->table('roles')
                        ->where('code', $value)
                        ->select('id')
                        ->fetchField();
                        
                    if ($roleId) {
                        $userIds = $this->database->table('user_roles')
                            ->where('role_id', $roleId)
                            ->select('user_id');
                            
                        $selection->where('id IN ?', $userIds);
                    }
                    break;
                    
                case 'is_active':
                case 'is_verified':
                    $selection->where($key, (bool) $value);
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
                    
                case 'last_login_before':
                    if ($value instanceof \DateTime) {
                        $selection->where('last_login <= ?', $value->format('Y-m-d H:i:s'));
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
        
        // Řazení
        if (property_exists('App\Model\User', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order('username ASC');
        }
        
        // Stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Konverze na entity
        $users = [];
        foreach ($selection as $row) {
            $users[] = User::fromArray($row->toArray());
        }
        
        return new PaginatedCollection(
            new Collection($users),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }
}