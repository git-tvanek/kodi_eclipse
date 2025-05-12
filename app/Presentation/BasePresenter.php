<?php

declare(strict_types=1);

namespace App\Presentation;

use Nette;
use Nette\Application\UI\Presenter;
use App\Facade\AuthorizationFacade;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
    /** @var AuthorizationFacade */
    protected AuthorizationFacade $authorizationFacade;
    
    /** @var int|null */
    protected ?int $currentUserId = null;
    
    /** @var bool */
    protected bool $userLoggedIn = false;
    
    /**
     * Injektuje AuthorizationFacade
     */
    public function injectAuthorizationFacade(AuthorizationFacade $authorizationFacade): void
    {
        $this->authorizationFacade = $authorizationFacade;
    }
    
    /**
     * Common initialization for all presenters
     */
    protected function startup(): void
    {
        parent::startup();
        
        // Získání informací o přihlášeném uživateli
        $this->userLoggedIn = $this->getUser()->isLoggedIn();
        $this->currentUserId = $this->userLoggedIn ? (int) $this->getUser()->getId() : null;
        
        // Předání informací o přihlášení do šablony
        $this->template->userLoggedIn = $this->userLoggedIn;
        $this->template->currentUserId = $this->currentUserId;
        
        // Pokud je uživatel přihlášený, předáme do šablony také jeho role a oprávnění
        if ($this->userLoggedIn) {
            $this->template->userPermissions = $this->authorizationFacade->getUserPermissions($this->currentUserId);
            
            // Základní role pro použití v šablonách
            $this->template->isAdmin = $this->authorizationFacade->hasRole($this->currentUserId, 'admin');
            $this->template->isEditor = $this->authorizationFacade->hasRole($this->currentUserId, 'editor');
            $this->template->isAuthor = $this->authorizationFacade->hasRole($this->currentUserId, 'author');
        }
    }

    /**
     * Before render initialization for all presenters
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        
        // Set common template variables
        $this->template->menuActive = $this->getName();
    }
    
    /**
     * Checks if user has permission for resource and action
     * 
     * @param string $resource Resource name
     * @param string $action Action name
     * @param bool $redirectOnFail Whether to redirect on fail
     * @param string $redirectTo Redirect destination if check fails
     * @return bool
     */
    protected function checkPermission(string $resource, string $action, bool $redirectOnFail = true, string $redirectTo = 'Homepage:'): bool
    {
        if (!$this->userLoggedIn) {
            if ($redirectOnFail) {
                $this->flashMessage('Pro tuto akci musíte být přihlášen', 'danger');
                $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
            }
            return false;
        }
        
        $isAllowed = $this->authorizationFacade->isAllowed($this->currentUserId, $resource, $action);
        
        if (!$isAllowed && $redirectOnFail) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'danger');
            $this->redirect($redirectTo);
        }
        
        return $isAllowed;
    }
    
    /**
     * Checks if user has a specific role
     * 
     * @param string $roleCode Role code
     * @param bool $redirectOnFail Whether to redirect on fail
     * @param string $redirectTo Redirect destination if check fails
     * @return bool
     */
    protected function checkRole(string $roleCode, bool $redirectOnFail = true, string $redirectTo = 'Homepage:'): bool
    {
        if (!$this->userLoggedIn) {
            if ($redirectOnFail) {
                $this->flashMessage('Pro tuto akci musíte být přihlášen', 'danger');
                $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
            }
            return false;
        }
        
        $hasRole = $this->authorizationFacade->hasRole($this->currentUserId, $roleCode);
        
        if (!$hasRole && $redirectOnFail) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'danger');
            $this->redirect($redirectTo);
        }
        
        return $hasRole;
    }
    
    /**
     * Checks if user has any of provided roles
     * 
     * @param array $roleCodes Array of role codes
     * @param bool $redirectOnFail Whether to redirect on fail
     * @param string $redirectTo Redirect destination if check fails
     * @return bool
     */
    protected function checkAnyRole(array $roleCodes, bool $redirectOnFail = true, string $redirectTo = 'Homepage:'): bool
    {
        if (!$this->userLoggedIn) {
            if ($redirectOnFail) {
                $this->flashMessage('Pro tuto akci musíte být přihlášen', 'danger');
                $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
            }
            return false;
        }
        
        $hasAnyRole = $this->authorizationFacade->hasAnyRole($this->currentUserId, $roleCodes);
        
        if (!$hasAnyRole && $redirectOnFail) {
            $this->flashMessage('Nemáte oprávnění k této akci', 'danger');
            $this->redirect($redirectTo);
        }
        
        return $hasAnyRole;
    }
    
    /**
     * Create flash message
     * 
     * @param string $message Message text
     * @param string $type Message type (success, info, warning, danger)
     */
    public function flashMessage(mixed $message, string $type = 'info'): \stdClass
    {
        return parent::flashMessage($message, $type);
    }
}