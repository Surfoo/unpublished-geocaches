<?php

require dirname(__DIR__) . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\Cookie\SetCookie;

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

$postdata = array('__EVENTTARGET'      => '',
                  '__EVENTARGUMENT'    => '',
                  'ctl00$ContentBody$tbUsername'   => $_POST['username'],
                  'ctl00$ContentBody$tbPassword'   => $_POST['password'],
                  'ctl00$ContentBody$cbRememberMe' => 'On',
                  'ctl00$ContentBody$btnSignIn'    => 'Login');

$cookieJar = new SessionCookieJar('cookie', true);
$client = new Client([
    'base_uri' => URL_LOGIN,
    'timeout'  => 2.0,
    'cookies' => $cookieJar
]);

try {
    $response = $client->request('POST', URL_LOGIN, [
        'form_params' => $postdata
    ]);
} catch(Exception $e) {
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
}


$htmlResponse = (string) $response->getBody();

if (!$htmlResponse) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}

if (!preg_match('/ctl00_ContentBody_lbUsername">.*<strong>(.*)<\/strong>/', $htmlResponse, $username)) {
    renderAjax(array('success' => false, 'message' => 'Your username/password combination does not match. Make sure you entered your information correctly.'));
}

$username = trim($username[1]);

$cookieJar->setCookie(new SetCookie(['username' => $username]));
$_SESSION['username'] = $username;

renderAjax(array('success' => true, 'username' => $username));
