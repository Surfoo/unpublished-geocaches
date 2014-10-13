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
define('GPX_FILENAME',      ROOT . '/web/gpx/%s.gpx');
define('COOKIE_FILENAME',   ROOT . '/cookies/cookie_%s');
define('MAX_RETENTION',     3600 * 24);

define('SUFFIX_CSS_JS', 20141013);

require ROOT . '/vendor/autoload.php';
require ROOT . '/helper.php';

session_start();

$header = array();
$header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
$header[] = "User-Agent: Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0";
$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
$header[] = "Accept-Language: fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3";
$header[] = "Keep-Alive: 115";
$header[] = "Connection: keep-alive";
$header[] = "Content-type: application/x-www-form-urlencoded;";
