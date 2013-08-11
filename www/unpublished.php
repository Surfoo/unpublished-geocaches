<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || 
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$cookie_filename = sprintf(COOKIE_FILENAME, md5($_SESSION['username']));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL_QUICKVIEW);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_filename);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$content = curl_exec($ch);
if(!$content) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}
curl_close($ch);

if(!preg_match_all('#<li>\s*<img src="https?://www.geocaching.com/images/wpttypes/sm/\d*.gif" width="16" height="16" alt="" />' .
                  '\s*<a href="https?://www.geocaching.com/seek/cache_details.aspx\?guid=(.*)">(.*)</a></li>#msU', $content, $elements)) {
    renderAjax(array('success' => false, 'message' => 'No unpublished caches found.'));
}
$unpublishedCaches = array_map('trim', array_combine($elements[1], $elements[2]));

if(empty($unpublishedCaches)) {
    renderAjax(array('success' => false, 'message' => 'Problem during recovery unpublished caches'));
}

renderAjax(array('success' => true, 'unpublishedCaches' => $unpublishedCaches));
