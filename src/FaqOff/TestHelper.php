<?php
namespace Soatok\FaqOff;

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Zend\Stdlib\ResponseInterface;

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
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$app->getContainer();
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
        self::$app->getContainer()['request'] = $request;
    }

    /**
     * @return Response
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public static function getResponse(): Response
    {
        $request = self::$app->getContainer()['request'];
        $response = self::$app->getContainer()['response'];
        return self::$app->process($request, $response);
    }
}
