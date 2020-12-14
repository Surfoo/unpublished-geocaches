<?php

require dirname(__DIR__) . '/app/app.php';

use Geocaching\GeocachingFactory;
use Geocaching\Exception\GeocachingSdkException;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;
use League\OAuth2\Client\Provider\Exception\GeocachingIdentityProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

$loader = new Twig\Loader\FilesystemLoader(TEMPLATE_DIR);
$twig   = new Twig\Environment($loader, ['debug' => TWIG_DEBUG, 'cache' => TEMPLATE_COMPILED_DIR]);

$twig_vars = ['user' => []];

// OAuth reset
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0);
    }
    session_destroy();
    header('Location: /');
    exit(0);
}

// Create Provider
$provider = new GeocachingProvider([
    'clientId'       => $app['oauth_key'],
    'clientSecret'   => $app['oauth_secret'],
    'redirectUri'    => $app['callback_url'],
    'response_type'  => 'code',
    'scope'          => '*',
    'environment'    => $app['environment'],
]);

// Run the OAuth process
if (isset($_POST['oauth'])) {
    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit(0);
}

// Return to the callback URL after the user gave the permission
if (isset($_SESSION['oauth2state'])) {
    // Check given state against previously stored one to mitigate CSRF attack
    if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
        $twig_vars['exception'] = [
            'type'    => 'Invalid State',
            'message' => $_GET['state'] . ' != ' . $_SESSION['oauth2state']
        ];
    } else {
        //in case of error

        // state is OK, retrieve the access token
        try {
            if (isset($_GET['error'])) {
                throw new GeocachingSdkException(sprintf('error:%s, error_description:%s', $_GET['error'], $_GET['error_description']));
            }
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            $_SESSION['accessToken']      = $accessToken->getToken();
            $_SESSION['refreshToken']     = $accessToken->getRefreshToken();
            $_SESSION['expiredTimestamp'] = $accessToken->getExpires();
            $_SESSION['hasExpired']       = $accessToken->hasExpired();
            $_SESSION['object']           = serialize($accessToken);
        } catch (IdentityProviderException $e) {
            $logger->error($e->getMessage());
            // Failed to get the access token or user details.
            $twig_vars['exception'] = [
                'type'    => 'IdentityProviderException',
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
                'trace'   => print_r($e->getTrace(), true),
            ];
        } catch (GeocachingSdkException $e) {
            echo $e->getMessage();
            die;
        }
    }

    unset($_SESSION['oauth2state']);
    header('Location: /');
    exit(0);
}

if (!empty($_SESSION['accessToken'])) {
    try {
        $accessToken = unserialize($_SESSION['object']);
        //Check expiration token, and renew
        if ($accessToken->hasExpired()) {
            try {
                $accessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $accessToken->getRefreshToken()
                ]);
            
                $_SESSION['accessrefreshTokenToken'] = $accessToken->getToken();
                $_SESSION['refreshToken']            = $accessToken->getRefreshToken();
                $_SESSION['expiredTimestamp']        = $accessToken->getExpires();
                $_SESSION['hasExpired']              = $accessToken->hasExpired();
                $_SESSION['object']                  = serialize($accessToken);
            } catch (GeocachingIdentityProviderException $e) {
                $logger->error($e->getMessage());

                $class = explode('\\', get_class($e));

                $twig_vars['exception'] = [
                    'type'    => array_pop($class),
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'trace'   => print_r($e->getTrace(), true),
                ];
            }
        }

        $sdk = GeocachingFactory::createSdk(
            $_SESSION['accessToken'],
            $app['environment'],
            [
                                                'connect_timeout' => $app['connect_timeout'],
                                                'timeout'         => $app['timeout'],
                                                'handler'         => $handlerStack,
                                            ]
        );

        $unpublished = new Unpublished($sdk);

        if (empty($_SESSION['user'])) {
            $_SESSION['user'] = $unpublished->getMyProfile();
        }

        $twig_vars['user'] = $_SESSION['user'];
    } catch (\Throwable $e) {
        $logger->error($e->getMessage());

        $class = explode('\\', get_class($e));

        $twig_vars['exception'] = [
            'type'    => array_pop($class),
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'trace'   => print_r($e->getTrace(), true),
        ];
    }
}

echo $twig->render('index.html.twig', $twig_vars);
