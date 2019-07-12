<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\AdminListDirFilter;
use Soatok\FaqOff\Filter\AdminMkdirFilter;

/**
 * Class AJAX
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class AJAX extends Endpoint
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function makeDirectory(RequestInterface $request): ResponseInterface
    {
        $filter = new AdminMkdirFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $parent = $post['parent'] ?? '';
            $dirname = $post['dirname'] ?? '';
            if (!$dirname) {
                return $this->json(
                    ['error' => 'Empty dirname'],
                    StatusCode::HTTP_NOT_ACCEPTABLE
                );
            }
            if (
                preg_match('#^\.+$#', $dirname)
                    ||
                strpos($dirname, '/')
                    ||
                strpos($dirname, '\\') !== false
            ) {
                return $this->json(
                    ['error' => 'Invalid dirname'],
                    StatusCode::HTTP_NOT_ACCEPTABLE
                );
            }

            $localFiles = APP_ROOT . '/public/local';
            $target = $localFiles;
            if ($parent) {
                $target .= '/' . trim($parent, '/');
            }

            $realpath = realpath($target);
            if (strpos($realpath, $localFiles) !== 0) {
                // Security check.
                return $this->json(
                    ['error' => 'File does not exist within public/local'],
                    StatusCode::HTTP_FORBIDDEN
                );
            }
            $target .= '/' . $dirname;
            if (is_dir($target)) {
                return $this->json(
                    ['error' => 'Directory already exists.'],
                    StatusCode::HTTP_SEE_OTHER
                );
            }
            mkdir($target, 0777);
            return $this->json(
                [
                    'status' => 'OK',
                    'directory' => $parent
                        ? '/local/' . trim($parent, '/') . '/' . $dirname
                        : '/local/' . $dirname
                ]
            );
        }
        return $this->json(
            ['error' => 'Could not create a new directory.'],
            StatusCode::HTTP_NOT_ACCEPTABLE
        );
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function listDirectory(RequestInterface $request): ResponseInterface
    {
        $filter = new AdminListDirFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $localFiles = APP_ROOT . '/public/local';
            $directory = $localFiles . $post['directory'];
            $realpath = realpath($directory);
            if ($realpath === false) {
                return $this->json(
                    ['error' => 'Directory does not exist within public/local'],
                    StatusCode::HTTP_FORBIDDEN
                );
            }
            if (strpos($realpath, $localFiles) !== 0) {
                // Security check.
                return $this->json(
                    ['error' => 'Directory does not exist within public/local'],
                    StatusCode::HTTP_FORBIDDEN
                );
            }
            if (!is_dir($realpath)) {
                return $this->json(
                    ['error' => 'This is not a directory'],
                    StatusCode::HTTP_NOT_FOUND
                );
            }
            $return = getcwd();
            chdir($realpath);
            $files = glob("*");
            chdir($return);

            // Process the directory contents:
            $listing = [];
            foreach ($files as $i => $f) {
                if ($f === '.' || $f === '..') {
                    continue;
                }
                $file = $realpath . '/' . $f;
                $listing[] = [
                    'name' => $f,
                    'size' => filesize($file),
                    'created' => (new \DateTime('@' . filectime($file)))
                        ->format(\DateTime::ISO8601),
                    'modified' => (new \DateTime('@' . filemtime($file)))
                        ->format(\DateTime::ISO8601),
                    'dir' => is_dir($file)
                ];
            }
            usort($listing, function (array $a, array $b) {
                if ($b['dir'] === $a['dir']) {
                    return $a['name'] <=> $b['name'];
                }
                return $b['dir'] <=> $a['dir'];
            });

            return $this->json(
                [
                    'status' => 'OK',
                    'files' => $listing
                ]
            );
        }
        return $this->json(
            ['error' => 'Could not list contents of this directory.'],
            StatusCode::HTTP_NOT_ACCEPTABLE
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $action = $routerParams['action'] ?? '';
        switch ($action) {
            case 'mkdir':
                return $this->makeDirectory($request);
            case 'ls':
                return $this->listDirectory($request);
            default:
                return $this->json(
                    ['error' => 'No action provided'],
                    StatusCode::HTTP_NOT_ACCEPTABLE
                );
        }
    }
}
