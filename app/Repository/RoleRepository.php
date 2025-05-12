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
}