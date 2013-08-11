<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' || 
    !array_key_exists('username', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

if(!array_key_exists('guid', $_POST) || empty($_POST['guid'])) {
    renderAjax(array('success' => false));
}

$waypoints = [];
foreach($_POST['guid'] as $guid) {
    $waypoint_filename = sprintf(WAYPOINT_FILENAME, $guid);
    if(file_exists($waypoint_filename)) {
        $waypoints[] = basename($waypoint_filename);
    }
}

$loader = new Twig_Loader_Filesystem(array(ROOT . '/waypoints/', TEMPLATE_DIR));
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$twig_vars['time'] = date('c');
$twig_vars['username'] = $_SESSION['username'];
$twig_vars['waypoints'] = $waypoints;

$gpx_file = $twig->render('geocaches.xml', $twig_vars);

$gpx_filename = sprintf(GPX_FILENAME, substr(md5($_SESSION['username'] . SALT), 0, 8));
$hd = fopen($gpx_filename, 'w');
fwrite($hd, $gpx_file);
fclose($hd);

renderAjax(array('success' => true,
                 'link' => '<a href="gpx/' . basename($gpx_filename) . '" class="btn btn-success" id="download-gpx">Download your GPX</a>'));
