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
    $additional_waypoint_filename = sprintf(ADDITIONAL_WAYPOINT_FILENAME, $guid);
    if (file_exists($additional_waypoint_filename)) {
        $additional_waypoint[] = basename($additional_waypoint_filename);
    }
}

$loader = new Twig_Loader_Filesystem(array(ROOT . '/waypoints/', TEMPLATE_DIR));
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$twig_vars['time'] = $twig_vars2['time'] = date('c');

$twig_vars['username'] = 'greasemonkey';
if (array_key_exists('username', $_SESSION)) {
    $twig_vars['username'] = $_SESSION['username'];
}

// Geocaches
$twig_vars['waypoints'] = $waypoints;
$gpx_file = $twig->render('geocaches.xml', $twig_vars);

// Additional waypoints
$twig_vars2['additional_waypoints'] = $additional_waypoint;
$additional_waypoints = $twig->render('additional_waypoints.xml', $twig_vars2);

if (array_key_exists('greasemonkey', $_POST)) {
    $gpx_prefix    = array_key_exists('ownerid', $_COOKIE) ? (int) $_COOKIE['ownerid'] : uniqid(sha1(rand()), true);
    $gpx_suffix    = SALT_GM;
    $id_link       = 'download-gpx-gm';
    $id_link_wpts  = 'download-wpts-gm';
} else {
    $gpx_prefix = $_SESSION['username'];
    $gpx_suffix = SALT;
    $id_link    = 'download-gpx';
    $id_link_wpts  = 'download-wpts';
}

$gpx_filename = sprintf(GPX_FILENAME, substr(md5($gpx_prefix . $gpx_suffix), 0, 12));
$hd = fopen($gpx_filename, 'w');
fwrite($hd, $gpx_file);
fclose($hd);

$additional_wpts_filename = sprintf(ADDITIONAL_GPX_FILENAME, substr(md5($gpx_prefix . $gpx_suffix), 0, 12));
$hd = fopen($additional_wpts_filename, 'w');
fwrite($hd, $additional_waypoints);
fclose($hd);

renderAjax(array('success' => true,
                 'link' => '<a href="gpx/' . basename($gpx_filename) . '" class="btn btn-success" id="' . $id_link .'">Download GPX</a>',
                 'link_wpts' => '<a href="gpx/' . basename($additional_wpts_filename) . '" class="btn btn-success" id="' . $id_link_wpts .'">Download Waypoints</a>'));
