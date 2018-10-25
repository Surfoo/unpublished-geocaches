<?php

$app = [
    'oauth_key'    => '',
    'oauth_secret' => '',
    'callback_url' => '',
    'environment'  => 'staging',
];

$app['connect_timeout'] = 5;
$app['timeout']         = 5;

define('ROOT', __DIR__);
define('TEMPLATE_DIR', ROOT . '/templates');
define('TEMPLATE_COMPILED_DIR', ROOT . '/cache');

define('URL_GEOCACHING', 'https://www.geocaching.com/');
define('SALT',           '');

define('WAYPOINT_FILENAME', ROOT . '/waypoints/%s.gpx');
define('GPX_FILENAME',      ROOT . '/web/gpx/%s.part%02d.gpx');
define('MAX_RETENTION',     3600 * 24);

define('SUFFIX_CSS_JS', '');
define('TWIG_DEBUG', false);

define('LOG_DIRECTORY', ROOT . '/logs/unpublished.log');

require ROOT . '/vendor/autoload.php';
require ROOT . '/helper.php';

session_start();
