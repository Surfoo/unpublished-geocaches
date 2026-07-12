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

    #[Route('/callback', name: 'app_callback')]
    public function callback(): RedirectResponse
    {
        return $this->redirectToRoute('app_homepage');
    }

    /**
     * Route only exists to give the "app_logout" name a path; the actual logout
     * is performed by the firewall's LogoutListener before this action ever runs.
     */
    #[Route('/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
        return $this->redirectToRoute('app_homepage');
    }
}
