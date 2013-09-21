<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('content', $_POST)) {
    renderAjax(array('success' => false, 'message' => 'Request empty.'));
}

$unpublished = new Unpublished();
$unpublished->setRawHtml($_POST['content']);
$unpublished->setGuid();
$unpublished->setGcCode();
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
    $message = count($unpublished->errors) > 1 ? 'Errors:' : 'Error:';
    $message.= "\n" . implode("\n", $unpublished->errors);
    renderAjax(array('success' => false, 'guid' => $unpublished->guid, 'message' => $message));
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

$list = array();
if (array_key_exists('unpublished', $_COOKIE)) {
    $list = json_decode($_COOKIE['unpublished'], true);
}

$list[$unpublished->guid] = $unpublished->urlname;

setcookie('ownerid', $unpublished->owner_id, time() + 3600 * 48);
setcookie('unpublished', json_encode($list), time() + 3600 * 48);

renderAjax(array('success' => true, 'guid' => $unpublished->guid));
