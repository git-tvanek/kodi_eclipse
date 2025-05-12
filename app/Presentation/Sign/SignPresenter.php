<?php

declare(strict_types=1);

namespace App\Presentation\Sign;

use App\Presentation\BasePresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

class SignPresenter extends BasePresenter
{
    /** @var string|null */
    private ?string $backlink = null;
    
    /**
     * Načtení parametru backlink
     */
    public function startup(): void
    {
        parent::startup();
        $this->backlink = $this->getParameter('backlink');
    }
    
    /**
     * Sign-in form factory.
     */
    protected function createComponentSignInForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte své uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte své heslo.');

        $form->addCheckbox('remember', 'Zapamatovat si mě');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * Sign-in form succeeded.
     */
    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
        try {
            $this->getUser()->setExpiration($values->remember ? '14 days' : '20 minutes');
            $this->getUser()->login($values->username, $values->password);
            
            $this->flashMessage('Přihlášení proběhlo úspěšně.', 'success');
            
            if ($this->backlink) {
                $this->restoreRequest($this->backlink);
            }
            
            $this->redirect('Home:');
            
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací údaje.');
        }
    }
    
    /**
     * Sign-up form factory.
     */
    protected function createComponentSignUpForm(): Form
    {
        $form = new Form;
        $form->addText('username', 'Uživatelské jméno:')
            ->setRequired('Prosím zadejte uživatelské jméno.');

        $form->addEmail('email', 'E-mail:')
            ->setRequired('Prosím zadejte e-mailovou adresu.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím zadejte heslo.')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Heslo znovu:')
            ->setRequired('Prosím zadejte heslo znovu.')
            ->addRule($form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Registrovat');

        $form->onSuccess[] = [$this, 'signUpFormSucceeded'];
        return $form;
    }

    /**
     * Sign-up form succeeded.
     */
    public function signUpFormSucceeded(Form $form, \stdClass $values): void
    {
        // Ve skutečné aplikaci by zde byla implementace pro registraci uživatele
        // $this->userManager->add($values->username, $values->email, $values->password);
        
        $this->flashMessage('Registrace proběhla úspěšně. Nyní se můžete přihlásit.', 'success');
        $this->redirect('Sign:in');
    }

    /**
     * Sign-out action.
     */
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení proběhlo úspěšně.', 'success');
        $this->redirect('Home:');
    }
    
    /**
     * Sign-in render
     */
    public function renderIn(): void
    {
        // Pokud je uživatel již přihlášen, přesměrujeme ho
        if ($this->userLoggedIn) {
            $this->flashMessage('Již jste přihlášen.', 'info');
            $this->redirect('Home:');
        }
    }
    
    /**
     * Sign-up render
     */
    public function renderUp(): void
    {
        // Pokud je uživatel již přihlášen, přesměrujeme ho
        if ($this->userLoggedIn) {
            $this->flashMessage('Již jste přihlášen.', 'info');
            $this->redirect('Home:');
        }
    }
}