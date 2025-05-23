<?php

declare(strict_types=1);

namespace App\Security;

use App\Service\IUserService;
use Nette\Security\SimpleIdentity;
use Nette\Security\Authenticator as NetteAuthenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\AuthenticationException;

/**
 * Autentizátor kompatibilní s novými verzemi Nette Security
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
     * @throws AuthenticationException
     */
    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $user = $this->userService->authenticate($username, $password);

        if (!$user) {
            throw new AuthenticationException('Neplatné přihlašovací údaje.');
        }

        $roles = [];
        $userWithRoles = $this->userService->getUserWithRoles($user->id);

        if ($userWithRoles && isset($userWithRoles['roles'])) {
            foreach ($userWithRoles['roles'] as $role) {
                $roles[] = $role->code;
            }
        }

        return new SimpleIdentity(
            $user->id,
            $roles,
            $user->getIdentityData()
        );
    }

    /**
     * Připraví identitu na uložení do session (nic neměníme)
     *
     * @param IIdentity $identity
     * @return IIdentity
     */
    public function sleepIdentity(IIdentity $identity): IIdentity
    {
        return $identity;
    }

    /**
     * Obnoví identitu ze session
     *
     * @param IIdentity $identity
     * @return IIdentity|null
     */
    public function wakeupIdentity(IIdentity $identity): ?IIdentity
    {
        $id = $identity->getId();
        $user = $this->userService->findById($id);

        if (!$user || !$user->is_active) {
            return null;
        }

        $roles = [];
        $userWithRoles = $this->userService->getUserWithRoles($id);

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
