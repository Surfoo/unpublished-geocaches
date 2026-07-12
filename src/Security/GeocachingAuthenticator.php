<?php

namespace App\Security;

use Geocaching\Enum\MembershipType;
use Geocaching\Utils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use League\OAuth2\Client\Provider\GeocachingResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GeocachingAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $apiLogger,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_callback';
    }

    public function authenticate(Request $request): Passport
    {
        $session  = $this->requestStack->getSession();
        $provider = $this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider();
        $provider->setPkceCode($session->get('oauth2_pkce_code'));

        $credentials = $this->fetchAccessToken($this->clientRegistry->getClient('geocaching_main'), [
            'code' => $request->query->get('code'),
        ]);

        $user = $this->getUser($credentials);

        $session->set('user', $user);

        return new SelfValidatingPassport(new UserBadge($credentials->getToken(), fn () => $user));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return null;
        }

        $this->apiLogger->info('onAuthenticationSuccess', [
            'referenceCode'     => $user->getReferenceCode(),
            'userId'            => $user->getUserId(),
            'username'          => $user->getUserIdentifier(),
            'membershipLevelId' => $user->getMembershipLevelId(),
        ]);

        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $this->requestStack->getSession();
        /** @var FlashBagInterface $flashBag */
        $flashBag = $session->getBag('flashes');
        $flashBag->add('error', $exception->getMessageKey());

        $this->apiLogger->error('onAuthenticationFailure', [
            'key'     => $exception->getMessageKey(),
            'message' => $exception->getMessageData(),
        ]);

        return new RedirectResponse($this->router->generate('app_homepage'));
    }

    private function getUser(AccessToken $credentials): User
    {
        $client = $this->clientRegistry->getClient('geocaching_main');
        /** @var GeocachingProvider $provider */
        $provider = $client->getOAuth2Provider();

        if (!in_array('avatarUrl', $provider->getResourceOwnerFields(), true)) {
            $provider->setResourceOwnerFields([...$provider->getResourceOwnerFields(), 'avatarUrl']);
        }

        /** @var GeocachingResourceOwner $geocachingResourceOwner */
        $geocachingResourceOwner = $client->fetchUserFromToken($credentials);

        $user = new User();
        $user->setUserId(Utils::referenceCodeToId($geocachingResourceOwner->getId()))
             ->setReferenceCode($geocachingResourceOwner->getId())
             ->setJoinedDateUtc(new \DateTime($geocachingResourceOwner->getJoinedDate()))
             ->setUsername($geocachingResourceOwner->getUsername())
             ->setCredentials($credentials)
             ->setAvatarUrl($geocachingResourceOwner->getAvatarUrl())
             ->setMembershipLevelId($geocachingResourceOwner->getMembershipLevelId())
        ;

        match ((int) $user->getMembershipLevelId()) {
            MembershipType::PREMIUM->id() => $user->setRoles(['ROLE_PREMIUM']),
            MembershipType::BASIC->id()   => $user->setRoles(['ROLE_BASIC']),
            default                       => $this->apiLogger->warning('Unknown membership level', [
                'membershipLevelId' => $user->getMembershipLevelId(),
                'username'          => $user->getUserIdentifier(),
            ]),
        };

        return $user;
    }
}
