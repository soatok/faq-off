<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use ParagonIE\Ionizer\InputFilterContainer;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\RequestInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;

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
            ->write($body);
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
        if (\method_exists($handler, 'init')) {
            $handler->init();
        }
        return $handler;
    }

    /**
     * @param RequestInterface $request
     * @param InputFilterContainer|null $filter
     * @return array
     */
    public static function getPostVars(
        RequestInterface $request,
        ?InputFilterContainer $filter = null
    ): array {
        if (\strtolower($request->getMethod()) !== 'post') {
            return [];
        }
        $array = [];
        \parse_str((string) $request->getBody(), $array);
        if (!\is_null($filter)) {
            try {
                return $filter($array);
            } catch (InvalidDataException $ex) {
                return [];
            }
        }
        return $array;
    }
}
