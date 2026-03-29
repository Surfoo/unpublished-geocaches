<?php

namespace App\Service;

use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use App\Security\User;
use Geocaching\ClientBuilder;
use Geocaching\Enum\Environment;
use Geocaching\GeocachingSdk;
use Geocaching\Options;
use League\OAuth2\Client\Provider\Exception\GeocachingIdentityProviderException;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GeocachingService
{
    public ?User $user = null;

    protected ?GeocachingProvider $provider = null;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private RequestStack $requestStack,
        private readonly ClientInterface $client,
        private readonly LoggerInterface $apiLogger,
        private readonly string $geocachingEnv,
    ) {
        $this->user = $this->requestStack->getSession()->get('user');
    }

    public function getProvider(): GeocachingProvider
    {
        if (is_null($this->provider)) {
            $this->provider = $this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider();
        }

        return $this->provider;
    }

    public function getGeocachingClientApi(): GeocachingSdk
    {
        $clientBuilder = new ClientBuilder($this->client);
        $options       = new Options([
            'access_token'   => $this->user->getCredentials()->getToken(),
            'environment'    => Environment::from($this->geocachingEnv),
            'client_builder' => $clientBuilder,
        ]);

        return new GeocachingSdk($options);
    }

    public function checkAndRefreshToken(): self
    {
        if ($this->user->getCredentials()->hasExpired()) {
            $this->refreshToken();
        }

        return $this;
    }

    public function refreshToken(): void
    {
        try {
            $credentials = $this->getProvider()->getAccessToken('refresh_token', [
                'refresh_token' => $this->user->getCredentials()->getRefreshToken(),
            ]);
            $this->user->setCredentials($credentials);
            $this->requestStack->getSession()->set('user', $this->user);

            $this->apiLogger->info('refreshToken', [
                'message'       => 'AccessToken has been refreshed',
                'referenceCode' => $this->user->getReferenceCode(),
                'username'      => $this->user->getUserIdentifier(),
            ]);
        } catch (GeocachingIdentityProviderException $e) {
            $this->apiLogger->warning('refreshToken', [
                'message'       => $e->getMessage(),
                'referenceCode' => $this->user->getReferenceCode(),
                'username'      => $this->user->getUserIdentifier(),
            ]);
            throw $e;
        }
    }

    public static function isClientError(int $statusCode): bool
    {
        return $statusCode >= 400 && $statusCode < 500;
    }

    public static function isServerError(int $statusCode): bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }
}
