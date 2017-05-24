<?php

require dirname(__DIR__) . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ||
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$client = new Client([
    'base_uri' => URL_QUICKVIEW,
    'timeout'  => 60,
    'cookies' => new SessionCookieJar('cookie', true)
]);

try {
    $response = $client->request('GET', URL_QUICKVIEW);
} catch(Exception $e) {
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
}

$htmlResponse = (string) $response->getBody();

if(!preg_match_all('#<div class="activity-data">\s+<p>\s+<a href="https://coord\.info/([A-Z0-9]+)"><strong>([^<]+)</strong></a>#msU', $htmlResponse, $elements)) {
    renderAjax(array('success' => false, 'message' => 'No unpublished caches found.'));
}
$unpublishedCaches = array_map('trim', array_combine($elements[1], $elements[2]));

if (empty($unpublishedCaches)) {
    renderAjax(array('success' => false, 'message' => 'Problem during recovery unpublished caches'));
}

asort($unpublishedCaches, SORT_NATURAL);

renderAjax(array('success' => true, 'count' => count($unpublishedCaches), 'unpublishedCaches' => $unpublishedCaches));
