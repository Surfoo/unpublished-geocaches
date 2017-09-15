<?php

require dirname(__DIR__) . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

// Sign out
if (array_key_exists('signout', $_POST)) {
    $_SESSION = array();
    session_destroy();
    renderAjax(array('success' => true, 'redirect' => true));
}

if (!array_key_exists('username', $_POST) || !array_key_exists('username', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

define('REQUEST_VERIFICATION_TOKEN', '__RequestVerificationToken');
define('PATTERN_LOGIN_NAME', '/<span class="user-name"[^>]*>(.+)<\/span>/');
define('PATTERN_AVATAR', '/<span class="user-avatar"[^>]*>\s+?<img src="([^"]+)"[^>]*>\s+?<\/span>/');

$postdata = array('Username'   => $_POST['username'],
                  'Password'   => $_POST['password']);

$cookieJar = new SessionCookieJar('cookie', true);
$client = new Client([
    'base_uri' => URL_LOGIN,
    'timeout'  => 5.0,
    'cookies' => $cookieJar
]);

try {
    $response = $client->get(URL_LOGIN);
} catch(RequestException $e) {
    renderAjax(array('success' => false, 'message' => 'Connection failed due to geocaching.com. Please retry later.'));
}

$htmlResponse = (string) $response->getBody();

if(preg_match(PATTERN_LOGIN_NAME, $htmlResponse, $username)) {
    $username = trim($username[1]);

    $cookieJar->setCookie(new SetCookie(['username' => $username]));
    $_SESSION['username'] = $username;

    renderAjax(array('success' => true, 'username' => $username));
}

$crawler = new Crawler($htmlResponse);
$postdata[REQUEST_VERIFICATION_TOKEN] = $crawler->filter('.login > form > input[name="' . REQUEST_VERIFICATION_TOKEN . '"]')->attr('value');

if(empty($postdata[REQUEST_VERIFICATION_TOKEN])) {
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
}

try {
    $response = $client->request('POST', URL_LOGIN, [
        'form_params' => $postdata
    ]);
} catch(RequestException $e) {
    renderAjax(array('success' => false, 'message' => 'Connection failed due to geocaching.com. Please retry later.'));
}

$htmlResponse = (string) $response->getBody();
if (!$htmlResponse) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}
if(!preg_match(PATTERN_LOGIN_NAME, $htmlResponse, $username)) {
    renderAjax(array('success' => false, 'message' => 'Either your username or password is incorrect. Please try again.'));
}

$username = trim($username[1]);
$cookieJar->setCookie(new SetCookie(['username' => $username]));
$_SESSION['username'] = $username;
renderAjax(array('success' => true, 'username' => $username));
