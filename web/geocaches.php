<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ||
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$errors = array();

$unpublished = new Unpublished();
$cache['guid'] = $_POST['guid'];
$unpublished->guid = $_POST['guid'];

$content = $unpublished->getCacheDetails();
if (!$content) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}

if (!$unpublished->setGcCode()) {
    renderAjax(array('success' => false, 'guid' => $cache['guid'], 'message' => 'Unable to retrieve the GC code.'));
}

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
    renderAjax(array('success' => false, 'guid' => $unpublished->guid, 'message' => implode('<br />', $unpublished->errors)));
}

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader);

$gpx_file = trim($twig->render('waypoint.xml', $unpublished->getGeocacheDatas()));

$hd = fopen(sprintf(WAYPOINT_FILENAME, $unpublished->guid), 'w');
fwrite($hd, $gpx_file);
fclose($hd);

renderAjax(array('success' => true, 'guid' => $unpublished->guid));
