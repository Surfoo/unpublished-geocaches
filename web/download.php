<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

if (!array_key_exists('guid', $_POST) || empty($_POST['guid'])) {
    renderAjax(array('success' => false, 'message' => 'No caches found.'));
}

$split = null;

if(isset($_POST['split'])) {
    $split = (int) $_POST['split'];
}

$waypointsList = array();

foreach ($_POST['guid'] as $guid) {
    $waypoint_filename = sprintf(WAYPOINT_FILENAME, $guid);
    if (file_exists($waypoint_filename)) {
        $waypointsList[] = basename($waypoint_filename);
    }
}

if($split > 0) {
    $waypointsList = array_chunk($waypointsList, $split);
} else {
    $waypointsListTmp[] = $waypointsList;
    unset($waypointsList);
    $waypointsList = $waypointsListTmp;
}

$loader = new Twig_Loader_Filesystem(array(ROOT . '/waypoints/', TEMPLATE_DIR));
$twig   = new Twig_Environment($loader);

$twig_vars['time'] = date('c');

$twig_vars['username'] = 'greasemonkey';
if (array_key_exists('username', $_SESSION)) {
    $twig_vars['username'] = $_SESSION['username'];
}

// Geocaches
$links = array();
foreach($waypointsList as $index => $waypoints) {
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

    $gpx_filename = sprintf(GPX_FILENAME, substr(md5($gpx_prefix . $gpx_suffix), 0, 12), $index+1);
    $hd = fopen($gpx_filename, 'w');
    fwrite($hd, $gpx_file);
    fclose($hd);
    $links[] = '<li><a href="gpx/' . basename($gpx_filename) . '" class="btn btn-success" id="' . $id_link .'">
                        <span class="glyphicon glyphicon-download"></span> Download GPX (part ' . ($index + 1) . ')</a></li>';
}

renderAjax(array('success' => true,
                 'link' => $links));
