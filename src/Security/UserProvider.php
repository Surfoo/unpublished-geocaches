<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = ($this->requestStack->getSession())->get('user');

        if (!$user instanceof User) {
            throw new UserNotFoundException('User not found');
        }

        if ($identifier !== $user->getUsername()) {
            throw new UserNotFoundException(sprintf('Invalid username: found %s, should be %s', $identifier, $user->getUsername()));
        }

        return $user;
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UsernameNotFoundException if the user no longer exists.
        $user = $this->loadUserByIdentifier($user->getUsername());

        // Si on arrive là, c'est que le token a expiré
        // Utilisation de token de l'utilisateur pour l'API et refresh du token
        //$this->api->setUser($user)->refreshToken();

        // Renvoie de l'objet User attendu, que le refresh ai fonctionné ou pas.
        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass($class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
