<?php

declare(strict_types=1);

namespace App\Forms;

use App\Model\Role;
use App\Service\IPermissionService;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class RolePermissionForm
{
    use SmartObject;
    
    /** @var IPermissionService */
    private IPermissionService $permissionService;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Konstruktor
     * 
     * @param IPermissionService $permissionService
     */
    public function __construct(IPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Role $role Role, pro kterou přiřazujeme oprávnění
     * @param array $assignedPermissionIds Seznam ID již přiřazených oprávnění
     * @return Form
     */
    public function create(Role $role, array $assignedPermissionIds = []): Form
    {
        $form = new Form();
        
        // Záhlaví formuláře
        $form->addHidden('role_id', (string)$role->id);
        
        // Získání všech oprávnění
        $permissions = $this->permissionService->findAll();
        
        // Příprava možností pro checkbox list
        $permissionOptions = [];
        foreach ($permissions as $permission) {
            $permissionOptions[$permission->id] = "{$permission->name} ({$permission->resource}:{$permission->action})";
        }
        
        // Vytvoření checkbox listu pro výběr oprávnění
        $form->addCheckboxList('permission_ids', 'Oprávnění:', $permissionOptions)
            ->setDefaultValue($assignedPermissionIds);
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', 'Uložit oprávnění');
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($role, $assignedPermissionIds) {
            $this->processForm($form, $values, $role, $assignedPermissionIds);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Role $role
     * @param array $assignedPermissionIds
     */
    private function processForm(Form $form, ArrayHash $values, Role $role, array $assignedPermissionIds): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Získání nově vybraných oprávnění
        $newPermissionIds = $data['permission_ids'] ?? [];
        
        // Vytvoření seznamu oprávnění k přidání a odebrání
        $permissionsToAdd = array_diff($newPermissionIds, $assignedPermissionIds);
        $permissionsToRemove = array_diff($assignedPermissionIds, $newPermissionIds);
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($role->id, $permissionsToAdd, $permissionsToRemove);
        }
    }
}