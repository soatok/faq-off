<?php
namespace Soatok\FaqOff;

use Slim\App;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

/**
 * Class TestHelper
 *
 * Meant to be used for PHPUnit
 *
 * @package Soatok\FaqOff
 */
abstract class TestHelper
{
    /** @var App $app */
    private static $app;

    /**
     * @param App $app
     * @return void
     */
    public static function injectApp(App $app)
    {
        self::$app = $app;
    }

    /**
     * @return App
     */
    public static function getApp(): App
    {
        return self::$app;
    }

    /**
     * @return Container
     */
    public static function getContainer(): Container
    {
        $c = self::$app->getContainer();
        if (!($c instanceof Container)) {
            throw new \TypeError('Not an instance of Slim Container');
        }
        return $c;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param array $cookies
     * @param array $server
     * @param string $body
     * @return void
     */
    public static function fakeRequest(
        string $method,
        string $uri = '/',
        array $headers = [],
        array $cookies = [],
        array $server = [],
        string $body = ''
    ) {
        $request = new Request(
            $method,
            Uri::createFromString($uri),
            new Headers($headers),
            $cookies,
            $server,
            Utility::stringToStream($body)
        );
        self::getContainer()['request'] = $request;
    }

    /**
     * @return Response
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public static function getResponse(): Response
    {
        $request = self::getContainer()['request'];
        $response = self::getContainer()['response'];
        $response = self::$app->process($request, $response);
        if (!($response instanceof Response)) {
            throw new \TypeError('Object not a Slim response');
        }
        return $response;
    }
}
