<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

// Sign out
if (array_key_exists('signout', $_POST)) {
    @unlink(sprintf(COOKIE_FILENAME, md5($_SESSION['username'])));
    $_SESSION = array();
    session_destroy();
    renderAjax(array('success' => true, 'redirect' => true));
}

if (!array_key_exists('username', $_POST) || !array_key_exists('username', $_POST)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

$cookie_filename = sprintf(COOKIE_FILENAME, md5($_POST['username']));

$hd = fopen($cookie_filename, 'w');
fclose($hd);
$postdata = array('__EVENTTARGET'      => '',
                  '__EVENTARGUMENT'    => '',
                  'ctl00$tbUsername'   => $_POST['username'],
                  'ctl00$tbPassword'   => $_POST['password'],
                  'ctl00$cbRememberMe' => 'On',
                  'ctl00$btnSignIn'    => 'Login');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL_LOGIN);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_filename);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
$res = curl_exec($ch);
if (!$res) {
    renderAjax(array('success' => false, 'message' => 'Request error: ' . curl_error($ch)));
}
curl_close($ch);

if (!preg_match('/ctl00_ContentBody_lbUsername">.*<strong>(.*)<\/strong>/', $res, $username)) {
    @unlink($cookie_filename);
    renderAjax(array('success' => false, 'message' => 'Your username/password combination does not match. Make sure you entered your information correctly.'));
}

$_SESSION['username'] = trim($username[1]);

renderAjax(array('success' => true, 'username' => $_SESSION['username']));
