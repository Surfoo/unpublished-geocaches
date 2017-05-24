<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ||
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    renderAjax(array('success' => false, 'message' => 'Bad request, are you logged?'));
    exit(0);
}

use GuzzleHttp\Cookie\SessionCookieJar;

$errors = array();

$cookieJar = new SessionCookieJar('cookie', true);

$unpublished = new Unpublished($cookieJar);

$cache['gccode'] = $_POST['gccode'];
$unpublished->name = $cache['gccode'];

if(!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 0;
}

if(++$_SESSION['counter'] >= 500) {
    sleep(60);
    $_SESSION['counter'] = 0;
}

$unpublished->setGuid();
$unpublished->getCacheDetails();

$unpublished->setSomeBasicInformations();

$unpublished->setCoordinates();
$unpublished->setCacheId();
$unpublished->setLocation();
$unpublished->setUsername();
//$unpublished->setOwnerId();
$unpublished->setShortDescription();
$unpublished->setLongDescription();
$unpublished->setEncodedHints();
$unpublished->setAttributes();
$unpublished->setWaypoints();

if (!empty($unpublished->errors)) {
    renderAjax(array('success' => false, 'gccode' => $unpublished->name, 'message' => '<strong>Unable to retrieve</strong>: ' . implode(', ', $unpublished->errors) . '.'));
}

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader);

$gpx_file = trim($twig->render('waypoint.xml', $unpublished->getGeocacheDatas()));

$hd = fopen(sprintf(WAYPOINT_FILENAME, $unpublished->name), 'w');
fwrite($hd, $gpx_file);
fclose($hd);

renderAjax(array('success' => true, 'gccode' => $unpublished->name));
