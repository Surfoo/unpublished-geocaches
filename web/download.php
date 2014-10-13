<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

if (!array_key_exists('guid', $_POST) || empty($_POST['guid'])) {
    renderAjax(array('success' => false, 'message' => 'No caches found.'));
}

$waypoints           = array();
$additional_waypoint = array();
foreach ($_POST['guid'] as $guid) {
    $waypoint_filename = sprintf(WAYPOINT_FILENAME, $guid);
    if (file_exists($waypoint_filename)) {
        $waypoints[] = basename($waypoint_filename);
    }
}

$loader = new Twig_Loader_Filesystem(array(ROOT . '/waypoints/', TEMPLATE_DIR));
$twig   = new Twig_Environment($loader);

$twig_vars['time'] = date('c');

$twig_vars['username'] = 'greasemonkey';
if (array_key_exists('username', $_SESSION)) {
    $twig_vars['username'] = $_SESSION['username'];
}

// Geocaches
$twig_vars['waypoints'] = $waypoints;
$gpx_file = $twig->render('geocaches.xml', $twig_vars);

$xml = new DomDocument();
$xml->preserveWhiteSpace = false;
$xml->formatOutput = true;
$xml->loadXML($gpx_file);
$gpx_file = $xml->saveXML();

if (array_key_exists('greasemonkey', $_POST)) {
    $gpx_prefix    = array_key_exists('ownerid', $_COOKIE) ? (int) $_COOKIE['ownerid'] : uniqid(sha1(rand()), true);
    $gpx_suffix    = SALT_GM;
    $id_link       = 'download-gpx-gm';
} else {
    $gpx_prefix = $_SESSION['username'];
    $gpx_suffix = SALT;
    $id_link    = 'download-gpx';
}

$gpx_filename = sprintf(GPX_FILENAME, substr(md5($gpx_prefix . $gpx_suffix), 0, 12));
$hd = fopen($gpx_filename, 'w');
fwrite($hd, $gpx_file);
fclose($hd);

$link = '<a href="gpx/' . basename($gpx_filename) . '" class="btn btn-success" id="' . $id_link .'"><span class="glyphicon glyphicon-download"></span> Download GPX</a>';

renderAjax(array('success' => true,
                 'link' => $link));
