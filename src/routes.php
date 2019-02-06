<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Soatok\FaqOff\Utility;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    $handler = Utility::getHandler('StaticPage', $this);
    return $handler($request);
});
