<?php

declare(strict_types=1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class UserForm
{
    use SmartObject;
    
    /** @var callable */
    public $onSuccess;
    
    /**
     * Vytvoří a nakonfiguruje registrační formulář
     * 
     * @return Form
     */
    public function createRegistrationForm(): Form
    {
        $form = new Form();
        
        // Základní informace
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte uživatelské jméno.')
            ->addRule(Form::MinLength, 'Uživatelské jméno musí mít alespoň %d znaky.', 3)
            ->addRule(Form::MaxLength, 'Uživatelské jméno může být maximálně %d znaků dlouhé.', 50);
            
        $form->addEmail('email', 'E-mail:')
            ->setRequired('Prosím zadejte e-mail.');
            
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte heslo.')
            ->addRule(Form::MinLength, 'Heslo musí mít alespoň %d znaků.', 8);
            
        $form->addPassword('password_confirm', 'Potvrzení hesla:')
            ->setRequired('Prosím potvrďte heslo.')
            ->addRule(Form::Equal, 'Hesla se neshodují.', $form['password']);
            
        // Ovládací prvky formuláře
        $form->addSubmit('register', 'Registrovat');
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) {
            $this->processRegistrationForm($form, $values);
        };
        
        return $form;
    }
    
    /**
     * Vytvoří a nakonfiguruje přihlašovací formulář
     * 
     * @return Form
     */
    public function createLoginForm(): Form
    {
        $form = new Form();
        
        // Přihlašovací údaje
        $form->addText('username', 'Uživatelské jméno nebo e-mail:')
            ->setRequired('Prosím zadejte uživatelské jméno nebo e-mail.');
            
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte heslo.');
            
        $form->addCheckbox('remember', 'Zapamatovat si mě')
            ->setDefaultValue(false);
            
        // Ovládací prvky formuláře
        $form->addSubmit('login', 'Přihlásit');
        
        // Zpracování formuláře
        $form->onSuccess[] = function (Form $form, ArrayHash $values) {
            $this->processLoginForm($form, $values);
        };
        
        return $form;
    }
    
    /**
     * Zpracuje odeslání registračního formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     */
    private function processRegistrationForm(Form $form, ArrayHash $values): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess('register', $data);
        }
    }
    
    /**
     * Zpracuje odeslání přihlašovacího formuláře
     * 
     * @param Form $form
     * @param ArrayHash $values
     */
    private function processLoginForm(Form $form, ArrayHash $values): void
    {
        // Převod ArrayHash na pole
        $data = (array) $values;
        
        // Volání callback funkce s připravenými daty
        if ($this->onSuccess) {
            $this->onSuccess('login', $data);
        }
    }
}