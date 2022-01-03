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

class GeocachingService {

    public ?User $user = null;

    protected ?GeocachingProvider $provider = null;

    protected GeocachingSdk $api;

    public function __construct(
        private ClientRegistry $clientRegistry,
        private RequestStack $requestStack,
        private readonly ClientInterface $client,
        private readonly LoggerInterface $apiLogger,
    ) {
        $this->user = ($this->requestStack->getSession())->get('user');
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
            'environment'    => Environment::from($_SERVER['GEOCACHING_ENV']), //TODO à améliorer en allant chercher la config dans le provider
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

            $this->getGeocachingClientApi();

            // $this->apiLogger->info('refreshToken', [
            //     'message'           => 'AccessToken has been refreshed',
            //     'referenceCode'     => $this->user->getReferenceCode(),
            //     'username'          => $this->user->getUserIdentifier(),
            // ]);
        } catch (GeocachingIdentityProviderException $e) {
            $this->apiLogger->warning('refreshToken', [
                'message'       => $e->getMessage(),
                'referenceCode' => $this->user->getReferenceCode(),
                'username'      => $this->user->getUserIdentifier(),
                //'responseBody'  => $e->getResponseBody(),
                //'trace'         => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Retourne le SDK de l'API geocaching pour utilisation en direct
     */
    public function getGeocachingApi(): GeocachingSdk
    {
        if (is_null($this->user)) {
            throw new \Exception('User missing');
        }

        return $this->getGeocachingClientApi();
    }

    public function isClientError(int $statusCode): bool
    {
        return $statusCode >= 400 && $statusCode < 500;
    }

    public function isServerError(int $statusCode): bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }
}