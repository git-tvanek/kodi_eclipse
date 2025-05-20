<?php

declare(strict_types=1);

namespace App\Forms;

use App\Entity\Permission;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class PermissionForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Permission|null $permission Existující oprávnění pro režim úpravy
     * @return Form
     */
    public function create(?Permission $permission = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Název oprávnění:')
            ->setRequired('Prosím zadejte název oprávnění.')
            ->addRule(Form::MaxLength, 'Název může být maximálně %d znaků dlouhý.', 100);
            
        $form->addText('resource', 'Zdroj:')
            ->setRequired('Prosím zadejte zdroj.')
            ->addRule(Form::MaxLength, 'Zdroj může být maximálně %d znaků dlouhý.', 100)
            ->setOption('description', 'Např. "addon", "user", "category"');
            
        $form->addText('action', 'Akce:')
            ->setRequired('Prosím zadejte akci.')
            ->addRule(Form::MaxLength, 'Akce může být maximálně %d znaků dlouhá.', 100)
            ->setOption('description', 'Např. "view", "edit", "delete"');
            
        $form->addTextArea('description', 'Popis:')
            ->setRequired(false)
            ->addRule(Form::MaxLength, 'Popis může být maximálně %d znaků dlouhý.', 255);
            
        // ID pro režim úpravy
        if ($permission) {
            $form->addHidden('id', (string)$permission->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $permission ? 'Uložit změny' : 'Přidat oprávnění');
        
        // Předvyplnění formuláře při úpravách
        if ($permission) {
            $form->setDefaults($permission->toArray());
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($permission) {
            $this->processForm($form, $values, $permission);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Permission|null $permission
     */
    private function processForm(Form $form, ArrayHash $values, ?Permission $permission): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $permission);
        }
    }
}