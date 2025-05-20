<?php

declare(strict_types=1);

namespace App\Forms;

use App\Entity\Role;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class RoleForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Vytvoří a nakonfiguruje formulář
     * 
     * @param Role|null $role Existující role pro režim úpravy
     * @return Form
     */
    public function create(?Role $role = null): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('name', 'Název role:')
            ->setRequired('Prosím zadejte název role.')
            ->addRule(Form::MaxLength, 'Název může být maximálně %d znaků dlouhý.', 100);
            
        $form->addText('code', 'Kód role:')
            ->setRequired('Prosím zadejte kód role.')
            ->addRule(Form::Pattern, 'Kód může obsahovat pouze malá písmena, čísla a pomlčky.', '[a-z0-9-]+')
            ->addRule(Form::MaxLength, 'Kód může být maximálně %d znaků dlouhý.', 50)
            ->setOption('description', 'Unikátní identifikátor role (např. "admin", "editor")');
            
        $form->addTextArea('description', 'Popis:')
            ->setRequired(false)
            ->addRule(Form::MaxLength, 'Popis může být maximálně %d znaků dlouhý.', 255);
            
        $form->addInteger('priority', 'Priorita:')
            ->setDefaultValue(0)
            ->setOption('description', 'Vyšší číslo znamená vyšší prioritu');
            
        // ID pro režim úpravy
        if ($role) {
            $form->addHidden('id', (string)$role->id);
        }
            
        // Ovládací prvky formuláře
        $form->addSubmit('save', $role ? 'Uložit změny' : 'Přidat roli');
        
        // Předvyplnění formuláře při úpravách
        if ($role) {
            $form->setDefaults($role->toArray());
        }
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($role) {
            $this->processForm($form, $values, $role);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     * @param Role|null $role
     */
    private function processForm(Form $form, ArrayHash $values, ?Role $role): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess($data, $role);
        }
    }
}