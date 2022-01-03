<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require dirname(__DIR__) . '/config.php';

$logger = new Logger('unpublished');
$logger->pushHandler(new StreamHandler(LOG_DIRECTORY, Logger::INFO));

$handlerStack = \GuzzleHttp\HandlerStack::create();
$handlerStack->push(\GuzzleHttp\Middleware::log($logger, new \GuzzleHttp\MessageFormatter('{method} {uri} HTTP/{version} {req_body} RESPONSE: {code} - {res_body}')));
