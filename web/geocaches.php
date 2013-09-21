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
$unpublished->setLocationUsername();
$unpublished->setOwnerId();
$unpublished->setShortDescription();
$unpublished->setLongDescription();
$unpublished->setEncodedHints();
$unpublished->setAttributes();
$unpublished->setWaypoints();

if (!empty($unpublished->errors)) {
    renderAjax(array('success' => false, 'guid' => $unpublished->guid, 'message' => implode('<br />', $unpublished->errors)));
}

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$gpx_file = $twig->render('waypoint.xml', $unpublished->getGeocacheDatas());

$hd = fopen(sprintf(WAYPOINT_FILENAME, $unpublished->guid), 'w');
fwrite($hd, $gpx_file);
fclose($hd);

$additional_waypoints = false;
if(is_array($unpublished->waypoints)) {
    $waypoint_file = $twig->render('additional_waypoint.xml', array('additional_waypoints' => $unpublished->waypoints, 'time' => date('c')));
    $hd = fopen(sprintf(ADDITIONAL_WAYPOINT_FILENAME, $unpublished->guid), 'w');
    fwrite($hd, $waypoint_file);
    fclose($hd);
    $additional_waypoints = true;
}
renderAjax(array('success' => true, 'guid' => $unpublished->guid, 'additional_waypoints' => $additional_waypoints));
