<?php

require dirname(__DIR__) . '/app/app.php';

use Geocaching\GeocachingFactory;
use Geocaching\Lib\Utils\Utils;
use Geocaching\Exception\GeocachingSdkException;
use League\OAuth2\Client\Provider\Geocaching as GeocachingProvider;

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ||
    !array_key_exists('accessToken', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    renderAjax(array('success' => false, 'message' => 'Bad request'));
}

$provider = new GeocachingProvider([
    'clientId'       => $app['oauth_key'],
    'clientSecret'   => $app['oauth_secret'],
    'redirectUri'    => $app['callback_url'],
    'response_type'  => 'code',
    'scope'          => '*',
    'environment'    => $app['environment'],
]);

$sdk = GeocachingFactory::createSdk($_SESSION['accessToken'], $app['environment'], ['connect_timeout' => $app['connect_timeout'], 'timeout' => $app['timeout'], 'handler' => $handlerStack]);

$unpublished = new Unpublished($sdk);

try {
    $geocaches = $unpublished->getGeocaches($_POST['geocodes']);
} catch (GeocachingSdkException $e) {
    $logger->error($e->getMessage());
    renderAjax(array('success' => false, 'message' => "API error:\n" . $e->getMessage()));
} catch (\Exception $e) {
    $errorMsg = json_decode($e->getMessage());
    if ($errorMsg) {
        $logger->error($errorMsg->message);
        renderAjax(array('success' => false, 'message' => $errorMsg->message));
    }
    renderAjax(array('success' => false, 'message' => $errorMsg));
}

$loader = new Twig\Loader\FilesystemLoader([ROOT . '/waypoints/', TEMPLATE_DIR]);
$twig   = new Twig\Environment($loader);
$filter = new \Twig\TwigFilter('referenceCodeToId', ['Geocaching\Lib\Utils\Utils', 'referenceCodeToId']);
$twig->addFilter($filter);

$failed = [];
$waypointsList = [];

//create each geocache file
foreach ($geocaches as $geocache) {
    $filename = sprintf(WAYPOINT_FILENAME, $geocache['referenceCode']);

    if (!$hd = fopen($filename, 'w')) {
        $failed[] = $geocache['referenceCode'];
        continue;
    }

    $waypointsList[] = basename($filename);

    fwrite($hd, trim($twig->render('waypoint.xml', $geocache)));
    fclose($hd);
}

// create gpx files
$twig_vars['username'] = $_SESSION['user']['username'];
$twig_vars['time'] = date('c');

$split = (int) $_POST['gpxSplit'];

if ($split > 0) {
    $waypointsList = array_chunk($waypointsList, $split);
} else {
    $waypointsListTmp[] = $waypointsList;
    unset($waypointsList);
    $waypointsList = $waypointsListTmp;
}


$links = [];

foreach ($waypointsList as $key => $waypoints) {
    $twig_vars['waypoints'] = $waypoints;
    $gpxContent = $twig->render('geocaches.xml', $twig_vars);

    $xml = new DomDocument();
    $xml->preserveWhiteSpace = false;
    $xml->formatOutput = true;
    $xml->loadXML($gpxContent);
    $gpxContent = $xml->saveXML();
    $gpxFilename = sprintf(GPX_FILENAME, substr(hash('sha512', $_SESSION['user']['referenceCode'] . SALT), 0, 12), $key + 1);
    $hd = fopen($gpxFilename, 'w');
    fwrite($hd, $gpxContent);
    fclose($hd);

    $linkName = 'Download GPX';
    if ($split > 0 && count($waypointsList) > 1) {
        $linkName.= ' (part ' . ($key + 1) . ')';
    }

    $links[] = '<li><a href="gpx/' . basename($gpxFilename) . '" class="btn btn-success" id="download-gpx">' .
               '<span class="glyphicon glyphicon-download"></span> ' . $linkName . '</a></li>';
}

renderAjax(array('success' => true, 'fail' => $failed, 'link' => $links));
