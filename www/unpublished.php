<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || 
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$cookie_filename = sprintf(COOKIE_FILENAME, md5($_SESSION['username']));

//Parse quickview page
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL_QUICKVIEW);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$content = curl_exec($ch);
curl_close($ch);

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($content, LIBXML_NOBLANKS);
$dom->formatOutput = true;
libxml_clear_errors();

$xpath = new \DomXPath($dom);

$elements = $xpath->query("//ul[preceding-sibling::h3[1][contains(., 'Your Unpublished Disabled Caches')]]");
if(!$elements || empty($elements->length)) {
    renderAjax(array('success' => false, 'message' => 'Pas de caches non publiées trouvées.'));
}

$unpublishedCaches = [];
foreach ($elements as $element) {
    foreach($element->childNodes as $item) {
        if(!isset($item->tagName))
            continue;
        $unpublishedCaches[substr($item->lastChild->getAttribute('href'), -36)] = trim($item->lastChild->nodeValue);
    }
}

if(empty($unpublishedCaches)) {
    renderAjax(array('success' => false, 'message' => 'Problème de récupération des caches non publiées'));
}

renderAjax(array('success' => true, 'unpublishedCaches' => $unpublishedCaches));
