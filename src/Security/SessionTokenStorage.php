<?php

namespace App\Security;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\TokenSet;
use League\OAuth2\Client\Token\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Persists the OAuth token set on the session-bound User, for the
 * surfoo/oauth2-geocaching TokenRefreshPlugin to read and update.
 *
 * This app is stateless (no database), so the "storage" is just the
 * current request's session. Locking is a no-op: PHP's native session
 * file locking already serializes concurrent requests for the same
 * session, so there is no real race to guard against here.
 */
class SessionTokenStorage implements TokenStorageInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getTokens(string $referenceCode): ?TokenSet
    {
        $user = $this->getUser($referenceCode);
        if (!$user instanceof User) {
            return null;
        }

        $credentials = $user->getCredentials();
        if (!$credentials instanceof AccessToken || $credentials->getRefreshToken() === null) {
            return null;
        }

        return TokenSet::create(
            $credentials->getToken(),
            $credentials->getRefreshToken(),
            max(($credentials->getExpires() ?? time()) - time(), 0),
        );
    }

    public function saveTokens(string $referenceCode, TokenSet $tokens): void
    {
        $user = $this->getUser($referenceCode);
        if (!$user instanceof User) {
            return;
        }

        $user->setCredentials(new AccessToken([
            'access_token'  => $tokens->accessToken,
            'refresh_token' => $tokens->refreshToken,
            'expires'       => $tokens->expiresAt->getTimestamp(),
        ]));

        $this->requestStack->getSession()->set('user', $user);
    }

    public function lockUser(string $referenceCode, int $timeoutSeconds = 30): bool
    {
        return true;
    }

    public function unlockUser(string $referenceCode): void
    {
    }

    public function isUserLocked(string $referenceCode): bool
    {
        return false;
    }

    private function getUser(string $referenceCode): ?User
    {
        $user = $this->requestStack->getSession()->get('user');

        if (!$user instanceof User || $user->getReferenceCode() !== $referenceCode) {
            return null;
        }

        return $user;
    }
}
