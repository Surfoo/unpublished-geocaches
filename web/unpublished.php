<?php

require dirname(__DIR__) . '/app/app.php';

use Geocaching\GeocachingFactory;
use Geocaching\Exception\GeocachingSdkException;

if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest' ||
    !array_key_exists('accessToken', $_SESSION)) {
    header("HTTP/1.0 400 Bad Request");
    exit(0);
}

try {
    $sdk = GeocachingFactory::createSdk($_SESSION['accessToken'],
                                        $app['environment'], ['connect_timeout' => $app['connect_timeout'], 
                                                              'timeout'         => $app['timeout'],
                                                              'handler'         => $handlerStack,
                                                              ]);

    $unpublished = new Unpublished($sdk);
    $geocaches = $unpublished->getUnpublishedGeocaches();
    // $geocaches = $unpublished->searchGeocaches('hby:Surfoo', 100);

} catch(GeocachingSdkException $e) {
    $logger->error($e->getMessage(), $e->getContext());
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
}

renderAjax(array('success' => true, 'count' => count($geocaches), 'geocaches' => $geocaches));