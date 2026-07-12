<?php

namespace App\Tests\Service;

use App\Security\User;
use App\Service\GeocachingService;
use Geocaching\GeocachingSdk;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GeocachingServiceTest extends TestCase
{
    private function makeService(User $user, SessionInterface $session): GeocachingService
    {
        $provider = $this->createStub(GeocachingProvider::class);

        $oauthClient = $this->createStub(OAuth2Client::class);
        $oauthClient->method('getOAuth2Provider')->willReturn($provider);

        $clientRegistry = $this->createStub(ClientRegistry::class);
        $clientRegistry->method('getClient')->willReturn($oauthClient);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getSession')->willReturn($session);

        return new GeocachingService(
            $clientRegistry,
            $requestStack,
            $this->createStub(ClientInterface::class),
            $this->createStub(LoggerInterface::class),
            'staging',
        );
    }

    public function testGetProviderReturnsOAuth2Provider(): void
    {
        $user = new User();
        $user->setUserId(1)->setUsername('tester')->setReferenceCode('PR01TEST')->setCredentials(new AccessToken([
            'access_token'  => 'tok',
            'refresh_token' => 'ref',
            'expires'       => time() + 3600,
        ]));

        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn($user);

        $service = $this->makeService($user, $session);

        $this->assertInstanceOf(GeocachingProvider::class, $service->getProvider());
    }

    public function testGetGeocachingClientApiBuildsSdkWithTokenRefreshEnabled(): void
    {
        $user = new User();
        $user->setUserId(1)->setUsername('tester')->setReferenceCode('PR01TEST')->setCredentials(new AccessToken([
            'access_token'  => 'tok',
            'refresh_token' => 'ref',
            'expires'       => time() + 3600,
        ]));

        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn($user);

        $service = $this->makeService($user, $session);

        $this->assertInstanceOf(GeocachingSdk::class, $service->getGeocachingClientApi());
    }

    public function testIsClientError(): void
    {
        $this->assertTrue(GeocachingService::isClientError(400));
        $this->assertTrue(GeocachingService::isClientError(404));
        $this->assertFalse(GeocachingService::isClientError(200));
        $this->assertFalse(GeocachingService::isClientError(500));
    }

    public function testIsServerError(): void
    {
        $this->assertTrue(GeocachingService::isServerError(500));
        $this->assertTrue(GeocachingService::isServerError(503));
        $this->assertFalse(GeocachingService::isServerError(200));
        $this->assertFalse(GeocachingService::isServerError(404));
    }
}
