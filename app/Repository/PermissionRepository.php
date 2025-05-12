<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Permission;
use App\Collection\Collection;
use App\Collection\PaginatedCollection;
use App\Repository\Interface\IPermissionRepository;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @extends BaseRepository<Permission>
 * @implements IPermissionRepository
 */
class PermissionRepository extends BaseRepository implements IPermissionRepository
{
    public function __construct(Explorer $database)
    {
        parent::__construct($database);
        $this->tableName = 'permissions';
        $this->entityClass = Permission::class;
    }

    /**
     * Najde oprávnění podle zdroje a akce
     * 
     * @param string $resource
     * @param string $action
     * @return Permission|null
     */
    public function findByResourceAndAction(string $resource, string $action): ?Permission
    {
        /** @var Permission|null */
        return $this->findOneBy([
            'resource' => $resource,
            'action' => $action
        ]);
    }
    
    /**
     * Najde oprávnění podle role
     * 
     * @param int $roleId
     * @return Collection<Permission>
     */
    public function findByRole(int $roleId): Collection
    {
        $permissions = [];
        $permissionRows = $this->database->table($this->tableName)
            ->select('permissions.*')
            ->joinWhere('role_permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $roleId);
        
        foreach ($permissionRows as $row) {
            $permissions[] = Permission::fromArray($row->toArray());
        }
        
        return new Collection($permissions);
    }
    
    /**
     * Najde oprávnění pro uživatele
     * 
     * @param int $userId
     * @return Collection<Permission>
     */
    public function findByUser(int $userId): Collection
    {
        // Spojení tabulek user_roles, role_permissions a permissions
        $permissions = [];
        $permissionRows = $this->database->query("
            SELECT DISTINCT p.*
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ?
        ", $userId);
        
        foreach ($permissionRows as $row) {
            $permissions[] = Permission::fromArray((array) $row);
        }
        
        return new Collection($permissions);
    }
    
    /**
     * Najde oprávnění s pokročilým filtrováním
     * 
     * @param array $filters Kritéria filtrování
     * @param string $sortBy Pole pro řazení
     * @param string $sortDir Směr řazení (ASC nebo DESC)
     * @param int $page Číslo stránky
     * @param int $itemsPerPage Počet položek na stránku
     * @return PaginatedCollection<Permission>
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
                case 'resource':
                case 'action':
                    $selection->where("$key LIKE ?", "%{$value}%");
                    break;
                    
                case 'role_id':
                    $selection->where('id IN ?', 
                        $this->database->table('role_permissions')
                            ->where('role_id', $value)
                            ->select('permission_id')
                    );
                    break;
                    
                case 'user_id':
                    $selection->where('id IN ?', 
                        $this->database->query("
                            SELECT DISTINCT rp.permission_id
                            FROM role_permissions rp
                            JOIN user_roles ur ON rp.role_id = ur.role_id
                            WHERE ur.user_id = ?
                        ", $value)->fetchAll()
                    );
                    break;
                
                default:
                    if (property_exists('App\Model\Permission', $key)) {
                        $selection->where($key, $value);
                    }
                    break;
            }
        }
        
        // Počet celkových výsledků
        $count = $selection->count();
        $pages = (int) ceil($count / $itemsPerPage);
        
        // Aplikace řazení
        if (property_exists('App\Model\Permission', $sortBy)) {
            $selection->order("{$sortBy} {$sortDir}");
        } else {
            $selection->order("name ASC"); // Výchozí řazení
        }
        
        // Aplikace stránkování
        $selection->limit($itemsPerPage, ($page - 1) * $itemsPerPage);
        
        // Konverze na entity
        $items = [];
        foreach ($selection as $row) {
            $items[] = Permission::fromArray($row->toArray());
        }
        
        return new PaginatedCollection(
            new Collection($items),
            $count,
            $page,
            $itemsPerPage,
            $pages
        );
    }
}