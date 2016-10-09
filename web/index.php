<?php

require dirname(__DIR__) . '/config.php';

use GuzzleHttp\Cookie\SessionCookieJar;

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$twig_vars['logged'] = 'false';

$cookieJar = new SessionCookieJar('cookie', true);

if (array_key_exists('username', $_SESSION) && array_key_exists('cookie', $_SESSION) && !empty($_SESSION['cookie'])) {
    $twig_vars['logged'] = 'true';
    $twig_vars['username'] = $_SESSION['username'];
}

echo $twig->render('index.tpl', $twig_vars);
