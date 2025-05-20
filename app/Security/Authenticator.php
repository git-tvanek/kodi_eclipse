<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\IUserService;
use Nette\Security\SimpleIdentity;
use Nette\Security\Authenticator as NetteAuthenticator;
use Nette\Security\IdentityHandler;

/**
 * Autentizátor kompatibilní s novějšími verzemi Nette
 */
class Authenticator implements NetteAuthenticator, IdentityHandler
{
    private IUserService $userService;
    
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }
    
    /**
     * Ověří uživatelské přihlašovací údaje
     * 
     * @param string $username
     * @param string $password
     * @return SimpleIdentity
     * @throws NetteAuthenticator\AuthenticationException
     */
    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $user = $this->userService->authenticate($username, $password);
        
        if (!$user) {
            throw new NetteAuthenticator\AuthenticationException('Neplatné přihlašovací údaje.', NetteAuthenticator::INVALID_CREDENTIAL);
        }
        
        // Získání rolí uživatele
        $userWithRoles = $this->userService->getUserWithRoles($user->id);
        $roles = [];
        
        if ($userWithRoles && isset($userWithRoles['roles'])) {
            foreach ($userWithRoles['roles'] as $role) {
                $roles[] = $role->code;
            }
        }
        
        // Vytvoření a vrácení Nette Identity
        return new SimpleIdentity(
            $user->id,
            $roles,
            $user->getIdentityData()
        );
    }
    
    /**
     * Načte identitu podle ID
     *
     * @param int $id
     * @return SimpleIdentity|null
     */
    public function sleepIdentity(SimpleIdentity $identity): SimpleIdentity
    {
        return $identity;
    }

    /**
     * Probudí identitu ze session
     *
     * @param SimpleIdentity $identity
     * @return SimpleIdentity
     */
    public function wakeupIdentity(SimpleIdentity $identity): ?SimpleIdentity
    {
        $id = $identity->getId();
        $user = $this->userService->findById($id);
        
        if (!$user || !$user->is_active) {
            return null;
        }
        
        // Aktualizujeme role, pokud se mezitím změnily
        $userWithRoles = $this->userService->getUserWithRoles($id);
        $roles = [];
        
        if ($userWithRoles && isset($userWithRoles['roles'])) {
            foreach ($userWithRoles['roles'] as $role) {
                $roles[] = $role->code;
            }
        }
        
        return new SimpleIdentity(
            $id,
            $roles,
            $user->getIdentityData()
        );
    }
}