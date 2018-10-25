<?php

require __DIR__ . '/app/app.php';

$now   = time();
$files = array_merge(glob(ROOT . '/waypoints/*.gpx'), glob(ROOT . '/web/gpx/*.gpx'));

foreach($files as $file) {
    if ($now > filemtime($file) + MAX_RETENTION && unlink($file)) {
        echo "deleted " . $file . "\n";
    }
}

echo "done.\n";