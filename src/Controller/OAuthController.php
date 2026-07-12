<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OAuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(ClientRegistry $clientRegistry, Request $request): Response
    {
        $response = $clientRegistry->getClient('geocaching_main')->redirect([], []);
        $pkceCode = $clientRegistry->getClient('geocaching_main')->getOAuth2Provider()->getPkceCode();

        $request->getSession()->set('oauth2_pkce_code', $pkceCode);

        return $response;
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/callback', name: 'app_callback')]
    public function callback(): RedirectResponse
    {
        return $this->redirectToRoute('app_homepage');
    }
}
