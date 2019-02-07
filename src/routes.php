<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Soatok\FaqOff\Utility;

// Routes

$app->any('/auth/{action:(?:login|logout|register)}', function (Request $request, Response $response, array $args) {
    $handler = Utility::getHandler('AuthGateway', $this);
    return $handler($request);
});

$app->get('/', function (Request $request, Response $response, array $args) {
    $handler = Utility::getHandler('StaticPage', $this);
    return $handler($request);
});

$c = $app->getContainer();
$c['notFoundHandler'] = function ($c) {
    return  Utility::getHandler('StaticPage', $c);
};
