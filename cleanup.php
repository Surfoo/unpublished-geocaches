<?php

require __DIR__ . '/config.php';

$now = time();
foreach(glob(ROOT . '/waypoints/*.gpx') as $file) {
    if($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "unlink ".$file."\n";
    }
}
foreach(glob(ROOT . '/cookies/cookie_*') as $file) {
    if($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "unlink ".$file."\n";
    }
}
foreach(glob(ROOT . '/www/gpx/*.gpx') as $file) {
    if($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "unlink ".$file."\n";
    }
}