<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\CSPBuilder\CSPBuilder;
use ParagonIE\Ionizer\InputFilterContainer;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\RequestInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Utility
 * @package Soatok\FaqOff
 */
abstract class Utility
{
    private static $container;

    /**
     * @param Container $container
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

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

    /**
     * Customize our Twig\Environment object
     *
     * @param Environment $env
     * @return Environment
     */
    public static function terraform(Environment $env): Environment
    {
        $container = self::$container;

        /**
         * @twig-filter cachebust
         * Usage: {{ "/static/main.css"|cachebust }}
         */
        $env->addFunction(
            new TwigFunction(
                'authorized',
                function () {
                    return !empty($_SESSION['account_id']);
                }
            )
        );
        $env->addFilter(
            new TwigFilter(
                'cachebust',
                function (string $filePath): string {
                    $realpath = realpath(CANIS_PUBLIC . '/' . trim($filePath, '/'));
                    if (!is_string($realpath)) {
                        return $filePath . '?__404notfound';
                    }

                    $sha384 = hash_file('sha384', $realpath, true);

                    return $filePath . '?' . Base64UrlSafe::encode($sha384);
                }
            )
        );
        $env->addFunction(
            new TwigFunction(
                'anti_csrf',
                function () {
                    return '<input type="hidden" name="csrf-protect" value="' .
                        Base64UrlSafe::encode($_SESSION['anti-csrf']) .
                        '" />';
                },
                ['is_safe' => ['html']]
            )
        );

        $env->addFunction(
            new TwigFunction(
                'csp_nonce',
                function (string $directive = 'script-src') use ($container) {
                    /** @var CSPBuilder $csp */
                    $csp = Utility::$container['csp'];
                    return $csp->nonce($directive);
                }
            )
        );

        $env->addFunction(
            new TwigFunction(
                'clear_message_once',
                function () {
                    $_SESSION['message_once'] = [];
                }
            )
        );

        $env->addGlobal('session', $_SESSION);

        return $env;
    }
}
