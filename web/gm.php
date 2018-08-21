<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('content', $_POST)) {
    renderAjax(array('success' => false, 'message' => 'Request empty.'));
}

use GuzzleHttp\Cookie\SessionCookieJar;

$cookieJar = new SessionCookieJar('cookie', true);

$username = '';
preg_match('#<span class="user-name">(.*)</span>#im', $_POST['content'], $matche);
if (!empty($matche)) {
    $username = $matche[1];
}

$unpublished = new Unpublished($cookieJar, $username);

try {
    $unpublished->setRawHtml($_POST['content']);

    $unpublished->setGuid();
    $unpublished->setGcCode();
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
} catch(\Exception $e) {
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
    exit;
}

if (!empty($unpublished->errors)) {
    $message = count($unpublished->errors) > 1 ? 'Errors:' : 'Error:';
    $message.= "\n" . implode("\n", $unpublished->errors);
    renderAjax(array('success' => false, 'message' => $message));
}

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$gpx_file = $twig->render('waypoint.xml', $unpublished->getGeocacheDatas());

$hd = fopen(sprintf(WAYPOINT_FILENAME, $unpublished->name), 'w');
fwrite($hd, $gpx_file);
fclose($hd);

$list = array();
if (array_key_exists('unpublished', $_COOKIE)) {
    $list = json_decode($_COOKIE['unpublished'], true);
}

$list[$unpublished->name] = $unpublished->urlname;

setcookie('ownerid', $unpublished->owner_id, time() + 3600 * 48);
setcookie('unpublished', json_encode($list), time() + 3600 * 48);

renderAjax(array('success' => true, 'gccode' => $unpublished->name));
