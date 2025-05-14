<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Role;
use App\Model\Permission;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IRoleRepository;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @extends BaseRepository<Role>
 * @implements IRoleRepository
 */
class RoleRepository extends BaseRepository implements IRoleRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'roles';
        $this->entityClass = Role::class;
    }

    /**
     * Najde roli podle kódu
     * 
     * @param string $code
     * @return Role|null
     */
    public function findByCode(string $code): ?Role
    {
        /** @var Role|null */
        return $this->findOneBy(['code' => $code]);
    }
    
    /**
     * Najde role s jejich oprávněními
     * 
     * @param int $roleId
     * @return array|null
     */
    public function getRoleWithPermissions(int $roleId): ?array
    {
        $role = $this->findById($roleId);
        
        if (!$role) {
            return null;
        }
        
        // Získání oprávnění pro roli
        $permissions = [];
        $permissionRows = $this->database->table('permissions')
            ->select('permissions.*')
            ->joinWhere('role_permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId);
        
        foreach ($permissionRows as $row) {
            $permissions[] = Permission::fromArray($row->toArray());
        }
        
        return [
            'role' => $role,
            'permissions' => new Collection($permissions)
        ];
    }
    
    /**
     * Najde role s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Role>
     */
    public function findWithFilters(array $filters = [], string $sortBy = 'name', string $sortDir = 'ASC', int $page = 1, int $itemsPerPage = 10): PaginatedCollection
    {
        $selection = $this->getTable();
        
        // Aplikace filtrů
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'name':
                case 'code':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'permission_id':
                    $selection->where('id IN ?', 
                        $this->database->table('role_permissions')
                            ->where('permission_id', $value)
                            ->select('role_id')
                    );
                    break;
                    
                case 'min_priority':
                    $selection->where('priority >= ?', (int) $value);
                    break;
                    
                case 'max_priority':
                    $selection->where('priority <= ?', (int) $value);
                    break;
                
                default:
                    if (property_exists('App\Model\Role', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Aplikace řazení
        if (property_exists('App\Model\Role', $sortBy)) {
            $selection->order("{$sortBy} {$sortDir}");
        } else {
            $selection->order("name ASC"); // Výchozí řazení
        }
        
        // Aplikace stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Konverze na entity
        $items = [];
        foreach ($selection as $row) {
            $items[] = Role::fromArray($row->toArray());
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
     * Přidá roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function addRolePermission(int $roleId, int $permissionId): bool
    {
        // Kontrola, zda vazba již existuje
        $exists = $this->database->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->count() > 0;
        
        if ($exists) {
            return true; // Vazba už existuje
        }
        
        // Vložení nové vazby
        $result = $this->database->table('role_permissions')->insert([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
        
        return $result !== false;
    }
    
    /**
     * Odebere roli oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function removeRolePermission(int $roleId, int $permissionId): bool
    {
        $result = $this->database->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
        
        return $result > 0;
    }
    
    /**
     * Získá role pro uživatele
     * 
     * @param int $userId
     * @return Collection<Role>
     */
    public function findRolesByUser(int $userId): Collection
    {
        $roles = [];
        $roleRows = $this->database->table($this->tableName)
            ->select('roles.*')
            ->joinWhere('user_roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->order('priority DESC'); // Prioritnější role první
        
        foreach ($roleRows as $row) {
            $roles[] = Role::fromArray($row->toArray());
        }
        
        return new Collection($roles);
    }

     /**
     * Zjistí, zda role existuje podle kódu
     * 
     * @param string $code
     * @return bool
     */
    public function existsByCode(string $code): bool
    {
        return $this->getTable()->where('code', $code)->count() > 0;
    }
    
    /**
     * Vytvoří novou roli
     * 
     * @param Role $role
     * @return int
     */
    public function create(Role $role): int
    {
        // Kontrola unikátnosti kódu
        if ($this->existsByCode($role->code)) {
            throw new \Exception("Role s kódem '{$role->code}' již existuje.");
        }
        
        return $this->save($role);
    }
    
    /**
     * Aktualizuje existující roli
     * 
     * @param Role $role
     * @return int
     */
    public function update(Role $role): int
    {
        // Kontrola unikátnosti kódu, pokud se změnil
        $originalRole = $this->findById($role->id);
        
        if ($originalRole && $originalRole->code !== $role->code && $this->existsByCode($role->code)) {
            throw new \Exception("Role s kódem '{$role->code}' již existuje.");
        }
        
        return $this->save($role);
    }
    
    /**
     * Najde role podle priority
     * 
     * @param int $priority
     * @param string $operator
     * @return Collection<Role>
     */
    public function findByPriority(int $priority, string $operator = '='): Collection
    {
        $roles = [];
        $query = "priority $operator ?";
        
        $rows = $this->getTable()->where($query, $priority);
        
        foreach ($rows as $row) {
            $roles[] = Role::fromArray($row->toArray());
        }
        
        return new Collection($roles);
    }
    
    /**
     * Najde role s vyšší nebo stejnou prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityHigherOrEqual(int $priority): Collection
    {
        return $this->findByPriority($priority, '>=');
    }
    
    /**
     * Najde role s nižší prioritou
     * 
     * @param int $priority
     * @return Collection<Role>
     */
    public function findByPriorityLower(int $priority): Collection
    {
        return $this->findByPriority($priority, '<');
    }
    
    /**
     * Vrátí všechna oprávnění pro roli
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function getRolePermissions(int $roleId): Collection
    {
        $permissions = [];
        
        $rows = $this->database->table('permissions')
            ->select('permissions.*')
            ->joinWhere('role_permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId);
            
        foreach ($rows as $row) {
            $permissions[] = Permission::fromArray($row->toArray());
        }
        
        return new Collection($permissions);
    }
    
    /**
     * Zjistí, zda role má konkrétní oprávnění
     * 
     * @param int $roleId
     * @param int $permissionId
     * @return bool
     */
    public function hasPermission(int $roleId, int $permissionId): bool
    {
        return $this->database->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->count() > 0;
    }
    
    /**
     * Zjistí, zda role má všechna oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAllPermissions(int $roleId, array $permissionIds): bool
    {
        $count = $this->database->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionIds)
            ->count();
            
        return $count === count($permissionIds);
    }
    
    /**
     * Zjistí, zda role má alespoň jedno oprávnění z daného seznamu
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     */
    public function hasAnyPermission(int $roleId, array $permissionIds): bool
    {
        return $this->database->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionIds)
            ->count() > 0;
    }
    
    /**
     * Vrátí počet uživatelů s danou rolí
     * 
     * @param int $roleId
     * @return int
     */
    public function countUsers(int $roleId): int
    {
        return $this->database->table('user_roles')
            ->where('role_id', $roleId)
            ->count();
    }
    
    /**
     * Odstraní roli a všechny její oprávnění
     * 
     * @param int $roleId
     * @return bool
     */
    public function deleteWithPermissions(int $roleId): bool
    {
        return $this->transaction(function() use ($roleId) {
            // Nejprve odstraníme vazby role-oprávnění
            $this->database->table('role_permissions')
                ->where('role_id', $roleId)
                ->delete();
                
            // Poté odstraníme vazby uživatel-role
            $this->database->table('user_roles')
                ->where('role_id', $roleId)
                ->delete();
                
            // Nakonec odstraníme samotnou roli
            return $this->delete($roleId) > 0;
        });
    }
    
    /**
     * Najde role podle filtru s paginací
     * 
     * @param array $criteria
     * @param string $sortBy
     * @param string $sortDir
     * @param int $page
     * @param int $itemsPerPage
     * @return PaginatedCollection<Role>
     */
    public function search(
        array $criteria, 
        string $sortBy = 'name', 
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
                case 'name':
                case 'code':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'description':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'permission_id':
                    $roleIds = $this->database->table('role_permissions')
                        ->where('permission_id', $value)
                        ->select('role_id');
                        
                    $selection->where('id IN ?', $roleIds);
                    break;
                    
                case 'min_priority':
                    $selection->where('priority >= ?', $value);
                    break;
                    
                case 'max_priority':
                    $selection->where('priority <= ?', $value);
                    break;
                    
                default:
                    if (property_exists('App\Model\Role', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Řazení
        if (property_exists('App\Model\Role', $sortBy)) {
            $selection->order("$sortBy $sortDir");
        } else {
            $selection->order('priority DESC, name ASC');
        }
        
        // Stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Konverze na entity
        $roles = [];
        foreach ($selection as $row) {
            $roles[] = Role::fromArray($row->toArray());
        }
        
        return new PaginatedCollection(
            new Collection($roles),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }
}