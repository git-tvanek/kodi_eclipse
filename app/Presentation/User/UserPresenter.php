<?php

declare(strict_types=1);

namespace App\Presentation\User;

use App\Presentation\BasePresenter;
use App\Facade\AddonFacade;
use App\Facade\ReviewFacade;
use Nette\Application\UI\Form;

class UserPresenter extends BasePresenter
{
    /** @var AddonFacade */
    private AddonFacade $addonFacade;
    
    /** @var ReviewFacade */
    private ReviewFacade $reviewFacade;
    
    /**
     * Constructor
     */
    public function __construct(
        AddonFacade $addonFacade,
        ReviewFacade $reviewFacade
    ) {
        $this->addonFacade = $addonFacade;
        $this->reviewFacade = $reviewFacade;
    }
    
    /**
     * Kontrola přihlášení uživatele před všemi akcemi
     */
    public function startup(): void
    {
        parent::startup();
        
        // Kontrola, že uživatel je přihlášen
        if (!$this->userLoggedIn) {
            $this->flashMessage('Pro přístup k profilu musíte být přihlášen', 'danger');
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
    }
    
    /**
     * Profile action - show user profile
     */
    public function renderProfile(): void
    {
        // Ve skutečné aplikaci by zde byla logika pro získání dat profilu uživatele
        // $userData = $this->userManager->getUserData($this->currentUserId);
        // $this->template->userData = $userData;
    }
    
    /**
     * My addons action - show user's addons
     */
    public function renderMyAddons(int $page = 1): void
    {
        $itemsPerPage = 10;
        
        // Získání doplňků uživatele
        $addons = $this->addonFacade->searchAddons('', ['author_id' => $this->currentUserId], $page, $itemsPerPage);
        
        $this->template->addons = $addons;
        $this->template->page = $page;
    }
    
    /**
     * My reviews action - show user's reviews
     */
    public function renderMyReviews(int $page = 1): void
    {
        $itemsPerPage = 10;
        
        // Získání recenzí uživatele
        $reviews = $this->reviewFacade->findReviews(['user_id' => $this->currentUserId], $page, $itemsPerPage);
        
        $this->template->reviews = $reviews;
        $this->template->page = $page;
    }
    
    /**
     * Edit profile form factory
     */
    protected function createComponentEditProfileForm(): Form
    {
        $form = new Form;
        
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Prosím zadejte uživatelské jméno');
        
        $form->addEmail('email', 'E-mail')
            ->setRequired('Prosím zadejte e-mail');
        
        $form->addText('fullname', 'Celé jméno');
        
        $form->addTextArea('bio', 'O mně')
            ->setHtmlAttribute('rows', 5);
        
        $form->addUpload('avatar', 'Profilový obrázek')
            ->addRule(Form::IMAGE, 'Profilový obrázek musí být ve formátu JPEG, PNG nebo GIF.');
        
        $form->addSubmit('save', 'Uložit změny');
        
        $form->onSuccess[] = [$this, 'editProfileFormSucceeded'];
        
        // Ve skutečné aplikaci by zde bylo nastavení výchozích hodnot
        // $userData = $this->userManager->getUserData($this->currentUserId);
        // $form->setDefaults($userData);
        
        return $form;
    }
    
    /**
     * Edit profile form succeeded
     */
    public function editProfileFormSucceeded(Form $form, \stdClass $values): void
    {
        // Ve skutečné aplikaci by zde byla logika pro uložení dat profilu
        // $this->userManager->updateUserData($this->currentUserId, $values);
        
        $this->flashMessage('Profil byl úspěšně aktualizován', 'success');
        $this->redirect('User:profile');
    }
    
    /**
     * Change password form factory
     */
    protected function createComponentChangePasswordForm(): Form
    {
        $form = new Form;
        
        $form->addPassword('currentPassword', 'Současné heslo')
            ->setRequired('Prosím zadejte současné heslo');
        
        $form->addPassword('newPassword', 'Nové heslo')
            ->setRequired('Prosím zadejte nové heslo')
            ->addRule(Form::MIN_LENGTH, 'Nové heslo musí mít alespoň %d znaků', 6);
        
        $form->addPassword('confirmPassword', 'Potvrzení nového hesla')
            ->setRequired('Prosím potvrďte nové heslo')
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['newPassword']);
        
        $form->addSubmit('save', 'Změnit heslo');
        
        $form->onSuccess[] = [$this, 'changePasswordFormSucceeded'];
        
        return $form;
    }
    
    /**
     * Change password form succeeded
     */
    public function changePasswordFormSucceeded(Form $form, \stdClass $values): void
    {
        // Ve skutečné aplikaci by zde byla logika pro změnu hesla
        // try {
        //     $this->userManager->changePassword($this->currentUserId, $values->currentPassword, $values->newPassword);
        //     $this->flashMessage('Heslo bylo úspěšně změněno', 'success');
        // } catch (\Exception $e) {
        //     $form->addError('Současné heslo je nesprávné');
        //     return;
        // }
        
        $this->flashMessage('Heslo bylo úspěšně změněno', 'success');
        $this->redirect('User:profile');
    }
}