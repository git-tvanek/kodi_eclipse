<?php

declare(strict_types=1);

namespace App\Forms;

use App\Entity\User;
use App\Service\IRoleService;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class UserRoleForm
{
    use SmartObject;
    
    /** @var IRoleService */
    private IRoleService $roleService;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Konstruktor
     * 
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param User $user Uživatel, pro kterého přiřazujeme role
     * @param array $assignedRoleIds Seznam ID již přiřazených rolí
     * @return Form
     */
    public function create(User $user, array $assignedRoleIds = []): Form
    {
        $form = new Form();
        
        // Záhlaví formuláře
        $form->addHidden('user_id', (string)$user->id);
        
        // Získání všech rolí
        $roles = $this->roleService->findAll();
        
        // Příprava možností pro checkbox list
        $roleOptions = [];
        foreach ($roles as $role) {
            $roleOptions[$role->id] = "{$role->name} ({$role->code})";
        }
        
        // Vytvoření checkbox listu pro výběr rolí
        $form->addCheckboxList('role_ids', 'Role:', $roleOptions)
            ->setDefaultValue($assignedRoleIds);
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', 'Uložit role');
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($user, $assignedRoleIds) {
            $this->processForm($form, $values, $user, $assignedRoleIds);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param User $user
     * @param array $assignedRoleIds
     */
    private function processForm(Form $form, ArrayHash $values, User $user, array $assignedRoleIds): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Získání nově vybraných rolí
        $newRoleIds = $data['role_ids'] ?? [];
        
        // Vytvoření seznamu rolí k přidání a odebrání
        $rolesToAdd = array_diff($newRoleIds, $assignedRoleIds);
        $rolesToRemove = array_diff($assignedRoleIds, $newRoleIds);
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($user->id, $rolesToAdd, $rolesToRemove);
        }
    }
}