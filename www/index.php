<?php

require dirname(__DIR__) . '/config.php';

$loader = new Twig_Loader_Filesystem(TEMPLATE_DIR);
$twig   = new Twig_Environment($loader, array('debug' => true, 'cache' => TEMPLATE_COMPILED_DIR));

$twig_vars['logged'] = 'false';
if(array_key_exists('username', $_SESSION) && file_exists(sprintf(COOKIE_FILENAME, md5($_SESSION['username'])))) {
    $twig_vars['logged'] = 'true';
    $twig_vars['username'] = $_SESSION['username'];
}

echo $twig->render('index.tpl', $twig_vars);
