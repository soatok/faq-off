<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;

/**
 * Class Utility
 * @package Soatok\FaqOff
 */
abstract class Utility
{
    /**
     * @param string $body
     * @param array $headers
     * @param int $statusCode
     * @return Response
     */
    public static function createResponse(
        string $body,
        array $headers = [],
        int $statusCode = 200
    ): Response {
        return (new Response($statusCode, new Headers($headers)))
            ->withBody(static::stream($body));
    }

    /**
     * @param string $data
     * @return Stream
     */
    public static function stream(string $data): Stream
    {
        $fp = \fopen('php://temp', 'wb');
        \fwrite($fp, $data);
        return new Stream($fp);
    }

    /**
     * @param string $class
     * @param Container $container
     * @return HandlerInterface
     */
    public static function getHandler(string $class, Container $container)
    {
        $class = \preg_replace('#/[^A-Za-z0-9_\\\\]+/#', '', $class);
        if (empty($class)) {
            throw new \Error('Class name cannot be empty');
        }
        /** @var string $fqcn Fully Qualified Class Name */
        $fqcn = 'Soatok\\FaqOff\\Handler\\' . $class;
        if (!\class_exists($fqcn)) {
            throw new \Error('Handler not found: '. $fqcn);
        }

        /** @var HandlerInterface $handler */
        $handler = new $fqcn;
        if (!\method_exists($handler, 'setContainer')) {
            throw new \Error('Handler is missing the setContainer() method.');
        }
        $handler->setContainer($container);
        return $handler;
    }
}
