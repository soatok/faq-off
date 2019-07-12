<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\CSPBuilder\CSPBuilder;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\Ionizer\InputFilterContainer;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\RequestInterface;
use Slim\Container;
use Slim\Http\Headers;
use Slim\Http\Response;
use Slim\Http\Stream;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class Utility
 * @package Soatok\FaqOff
 */
abstract class Utility
{
    /** @var Container $container */
    private static $container;

    /**
     * @param Container $container
     * @return void
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * @return EasyDB
     * @throws \Interop\Container\Exception\ContainerException
     */
    public static function getDatabase(): EasyDB
    {
        return self::$container->get('database');
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
     * @return int[]
     * @throws \Interop\Container\Exception\ContainerException
     */
    public static function getAdminAccountIDs(): array
    {
        if (empty(self::$container['settings']['admin-accounts'])) {
            if (is_readable(APP_ROOT . '/local/admins.json')) {
                $data = json_decode(
                    file_get_contents(APP_ROOT . '/local/admins.json'),
                    true
                );
                if (is_array($data) && !empty($data)) {
                    self::$container['settings']['admin-accounts'] = $data;
                }
            }
        }
        return self::$container['settings']['admin-accounts'] ?? [];
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
     * @throws \Interop\Container\Exception\ContainerException
     */
    public static function terraform(Environment $env): Environment
    {
        $container = self::$container;

        $env->addFunction(
            new TwigFunction(
                'faqoff_theme_css',
                /**
                 * @param string|int|null $themeId
                 * @return array
                 */
                function($themeId = null) {
                    return Utility::getThemeData('css', $themeId);
                }
            )
        );
        $env->addFunction(
            new TwigFunction(
                'faqoff_theme_js',
                /**
                 * @param string|int|null $themeId
                 * @return array
                 */
                function($themeId = null) {
                    return Utility::getThemeData('js', $themeId);
                }
            )
        );
        $env->addFunction(
            new TwigFunction(
                'faqoff_theme_vars',
                /**
                 * @param string|int|null $themeId
                 * @return array
                 */
                function($themeId = null) {
                    return Utility::getThemeData('twig', $themeId);
                }
            )
        );

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
                    $realpath = realpath(FAQOFF_PUBLIC . '/' . trim($filePath, '/'));
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

        $env->addFunction(
            new TwigFunction(
                'is_admin',
                /** @return bool */
                function () {
                    return in_array(
                        $_SESSION['account_id'],
                        Utility::getAdminAccountIDs(),
                        true
                    );
                }
            )
        );

        $settings = $container->get('settings')['twig-custom'] ?? [];
        $env->addGlobal('faqoff_custom', $settings);
        $env->addGlobal('theme_id', null);

        $env->addGlobal('session', $_SESSION);

        return $env;
    }

    /**
     * @param string $type
     * @param string|int|null $themeId
     * @return array
     * @throws \Interop\Container\Exception\ContainerException
     */
    public static function getThemeData($type, $themeId = null): array
    {
        if (!$themeId) {
            return [];
        }
        switch ($type) {
            case 'css':
                $column = 'css_files';
                break;
            case 'js':
                $column = 'js_files';
                break;
            case 'twig':
                $column = 'twig_vars';
                break;
            default:
                return [];
        }
        $db = self::getDatabase();
        $body = $db->cell(
            "SELECT {$column} FROM faqoff_themes WHERE themeid = ?",
            $themeId
        );
        if (empty($body)) {
            return [];
        }
        $decoded = json_decode($body, true);
        return $decoded;
    }

    /**
     * @param string $body
     * @return Stream
     */
    public static function stringToStream(string $body): Stream
    {
        $resource = \fopen('php://temp', 'wb');
        \fwrite($resource, $body);
        return new Stream($resource);
    }

    /**
     * @param string $input
     * @return string
     */
    public function validateJson(string $input): string
    {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            return $input;
        }
        return '[]';
    }
}
