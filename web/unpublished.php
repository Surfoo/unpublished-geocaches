<?php

require dirname(__DIR__) . '/app/app.php';

use Geocaching\GeocachingFactory;
use Geocaching\Exception\GeocachingSdkException;

if (!array_key_exists('accessToken', $_SESSION)) {
    header('HTTP/1.0 403 Forbidden');
    $logger->error($e->getMessage(), $e->getContext());
    renderAjax(array('success' => false, 'message' => "accessToken missing"));
}

try {
    $sdk = GeocachingFactory::createSdk($_SESSION['accessToken'],
                                        $app['environment'], ['connect_timeout' => $app['connect_timeout'], 
                                                              'timeout'         => $app['timeout'],
                                                              'handler'         => $handlerStack,
                                                              ]);

    $unpublished = new Unpublished($sdk);

    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

    if ($contentType === "application/json") {
        $data = json_decode(trim(file_get_contents("php://input")), true);

        if(!isset($data['geocodes'])) {
            renderAjax(array('success' => false, 'message' => 'invalid data'));
        }

        preg_match_all('/(GC[a-z-0-9]+)/mi', $data['geocodes'], $matches);

        if (!isset($matches[1]) || empty($matches[1])) {
            renderAjax(array('success' => false, 'message' => 'no valid geocodes found'));
        }

        $geocodes = array_unique($matches[1]);

        $geocaches = $unpublished->getGeocaches($geocodes, true);
    } else {
        $geocaches = $unpublished->getUnpublishedGeocaches();
        // $geocaches = $unpublished->searchGeocaches('hby:Surfoo', 100);
    }
} catch(GeocachingSdkException $e) {
    header('HTTP/1.0 403 Forbidden');
    $logger->error($e->getMessage(), $e->getContext());
    renderAjax(array('success' => false, 'message' => $e->getMessage()));
}

renderAjax(array('success' => true, 'count' => count($geocaches), 'geocaches' => $geocaches));