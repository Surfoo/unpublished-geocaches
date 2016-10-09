<?php

error_reporting(-1);
ini_set('display_errors', '1');


define('ROOT', __DIR__);
define('TEMPLATE_DIR', ROOT . '/templates');
define('TEMPLATE_COMPILED_DIR', ROOT . '/cache');

define('URL_GEOCACHING', 'https://www.geocaching.com/');
define('URL_LOGIN',      URL_GEOCACHING . 'login/default.aspx');
define('URL_QUICKVIEW',  URL_GEOCACHING . 'my/');
define('URL_GEOCACHE',   URL_GEOCACHING . 'seek/cache_details.aspx?guid=%s');
define('URL_TILE',       'http://tiles%02d.geocaching.com/map.details?i=%s');
define('SALT',           'kequahmo4tainai1da9M');
define('SALT_GM',        'ooNa2aitejeipaiw8iet');

define('WAYPOINT_FILENAME', ROOT . '/waypoints/%s.gpx');
define('GPX_FILENAME',      ROOT . '/web/gpx/%s.part%02d.gpx');
define('MAX_RETENTION',     3600 * 24);

define('SUFFIX_CSS_JS', '20161009');

require ROOT . '/vendor/autoload.php';
require ROOT . '/helper.php';

session_start();