<?php

require dirname(__DIR__) . '/config.php';

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

// From greasemonkey
if(array_key_exists('unpublished', $_COOKIE) && !empty($_COOKIE['unpublished'])) {
    $gm_list = json_decode($_COOKIE['unpublished'], true);
    if($gm_list && is_array($gm_list) && !empty($gm_list)) {
        foreach($gm_list as $guid => $title) {
            $waypoint_filename = sprintf(WAYPOINT_FILENAME, $guid);
            if(!file_exists($waypoint_filename)) {
                unset($gm_list[$guid]);
            }
        }
        setcookie('unpublished', json_encode($gm_list), time() + 3600 * 48);
        renderAjax(array('success' => true, 'unpublishedCaches' => $gm_list));
    }
}

renderAjax(array('success' => true));
