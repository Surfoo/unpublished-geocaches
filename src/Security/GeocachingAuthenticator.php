<?php

namespace App\Security;

use Geocaching\Enum\MembershipType;
use Geocaching\Utils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GeocachingAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private RouterInterface $router,
        private RequestStack $requestStack,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'app_callback';
    }

    public function authenticate(Request $request): Passport
    {
        $session = $this->requestStack->getSession();

        $this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider()->setPkceCode($session->get('oauth2_pkce_code'));

        $credentials = $this->fetchAccessToken($this->clientRegistry->getClient('geocaching_main'), [
            'code' => $request->get('code')
        ]);

        $user = $this->getUser($credentials);

        $session->set('user', $user);

        return new SelfValidatingPassport(new UserBadge($credentials->getToken(), fn() => $this->getUser($credentials)));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add(
            'error',
            'Erreur de connexion'
        );
        dd('Erreur de connexion');
        return new RedirectResponse($this->router->generate('app_homepage'));
    }

    private function getUser(AccessToken $credentials): User
    {
        $this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider()->setResourceOwnerFields(
            [...$this->clientRegistry->getClient('geocaching_main')->getOAuth2Provider()->getResourceOwnerFields(), ...['avatarUrl']]
        );

        $geocachingResourceOwner = $this->clientRegistry->getClient('geocaching_main')->fetchUserFromToken($credentials);

        $user = new User();
        $user->setUserId(Utils::referenceCodeToId($geocachingResourceOwner->getId()))
             ->setReferenceCode($geocachingResourceOwner->getId())
             ->setJoinedDateUtc(new \DateTime($geocachingResourceOwner->getJoinedDate()))
             ->setUsername($geocachingResourceOwner->getUsername())
             ->setCredentials($credentials)
             ->setAvatarUrl($geocachingResourceOwner->getAvatarUrl())
             ->setMembershipLevelId($geocachingResourceOwner->getMembershipLevelId())
            ;

        switch ($user->getMembershipLevelId()) {
            case MembershipType::PREMIUM->id():
                $user->setRoles(['ROLE_PREMIUM']);
                break;
            case MembershipType::BASIC->id():
                $user->setRoles(['ROLE_BASIC']);
                break;
        }

        return $user;
    }
//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        /*
//         * If you would like this class to control what happens when an anonymous user accesses a
//         * protected page (e.g. redirect to /login), uncomment this method and make this class
//         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntrypointInterface.
//         *
//         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
//         */
//    }
}
