<?php

namespace App\Tests\Security;

use App\Security\SessionTokenStorage;
use App\Security\User;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\TokenSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionTokenStorageTest extends TestCase
{
    public function testGetTokensReturnsNullWhenReferenceCodeDoesNotMatch(): void
    {
        $user = new User();
        $user->setUserId(1)->setUsername('tester')->setReferenceCode('PR01TEST')->setCredentials(new AccessToken([
            'access_token'  => 'tok',
            'refresh_token' => 'ref',
            'expires'       => time() + 3600,
        ]));

        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn($user);

        $storage = new SessionTokenStorage($this->makeRequestStack($session));

        $this->assertNull($storage->getTokens('OTHER'));
    }

    public function testGetTokensReturnsTokenSetForMatchingUser(): void
    {
        $user = new User();
        $user->setUserId(1)->setUsername('tester')->setReferenceCode('PR01TEST')->setCredentials(new AccessToken([
            'access_token'  => 'tok',
            'refresh_token' => 'ref',
            'expires'       => time() + 3600,
        ]));

        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn($user);

        $storage = new SessionTokenStorage($this->makeRequestStack($session));
        $tokens  = $storage->getTokens('PR01TEST');

        $this->assertInstanceOf(TokenSet::class, $tokens);
        $this->assertSame('tok', $tokens->accessToken);
        $this->assertSame('ref', $tokens->refreshToken);
    }

    public function testSaveTokensUpdatesSessionUserCredentials(): void
    {
        $user = new User();
        $user->setUserId(1)->setUsername('tester')->setReferenceCode('PR01TEST')->setCredentials(new AccessToken([
            'access_token'  => 'old',
            'refresh_token' => 'ref',
            'expires'       => time() - 100,
        ]));

        /** @var SessionInterface&MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->method('get')->willReturn($user);
        $session->expects($this->once())->method('set')->with('user', $this->isInstanceOf(User::class));

        $storage = new SessionTokenStorage($this->makeRequestStack($session));
        $storage->saveTokens('PR01TEST', TokenSet::create('new', 'ref_new', 3600));

        $this->assertSame('new', $user->getCredentials()->getToken());
    }

    public function testLockUserAlwaysSucceeds(): void
    {
        $storage = new SessionTokenStorage($this->makeRequestStack($this->createStub(SessionInterface::class)));

        $this->assertTrue($storage->lockUser('PR01TEST'));
        $this->assertFalse($storage->isUserLocked('PR01TEST'));
    }

    private function makeRequestStack(SessionInterface $session): RequestStack
    {
        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        return $requestStack;
    }
}
