<?php

namespace App\Service;

use App\Security\SessionTokenStorage;
use App\Security\User;
use Geocaching\ClientBuilder;
use Geocaching\Enum\Environment;
use Geocaching\GeocachingSdk;
use Geocaching\Options;
use Http\Client\Common\Plugin\ErrorPlugin;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GeocachingService
{
    public ?User $user = null;

    protected ?GeocachingProvider $provider = null;

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RequestStack $requestStack,
        private readonly ClientInterface $client,
        private readonly LoggerInterface $apiLogger,
        private readonly string $geocachingEnv,
    ) {
        $this->user = $this->requestStack->getSession()->get('user');
    }

    public function getProvider(): GeocachingProvider
    {
        if (is_null($this->provider)) {
            /** @var GeocachingProvider $provider */
            $provider       = $this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider();
            $this->provider = $provider;
        }

        return $this->provider;
    }

    public function getGeocachingClientApi(): GeocachingSdk
    {
        $clientBuilder = new ClientBuilder($this->client);
        $tokenStorage  = new SessionTokenStorage($this->requestStack);

        $options = new Options([
            'access_token'   => $this->user->getCredentials()->getToken(),
            'environment'    => Environment::from($this->geocachingEnv),
            'client_builder' => $clientBuilder,
            'token_storage'  => $tokenStorage,
            'reference_code' => $this->user->getReferenceCode(),
        ]);

        $options->enableTokenRefresh([
            'reference_code' => $this->user->getReferenceCode(),
            'storage'        => $tokenStorage,
            'oauth_provider' => $this->getProvider(),
            'logger'         => $this->apiLogger,
        ]);

        // Turns non-2xx responses into exceptions so the retry plugin below can act on them
        // (429/500/502/503/504 are retried with backoff; 429 honors the API's x-rate-limit-reset header).
        $options->getClientBuilder()->addPlugin(new ErrorPlugin());
        $options->configureRetry([
            'max_attempts'  => 5,
            'base_delay_ms' => 500,
            'max_delay_ms'  => 30000,
            'logger'        => $this->apiLogger,
        ]);

        return new GeocachingSdk($options);
    }

    public function createUnpublishedService(): UnpublishedService
    {
        return new UnpublishedService($this->getGeocachingClientApi());
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
