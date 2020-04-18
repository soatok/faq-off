<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Exceptions\AdminFileException;
use Soatok\FaqOff\Filter\{
    AdminCreateFileFilter,
    AdminEditFileFilter
};
use Soatok\FaqOff\MessageOnceTrait;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};
/**
 * Class CustomContent
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class CustomContent extends BackendEndpoint
{
    use MessageOnceTrait;

    /**
     * Guards filename against LFI attacks, returns full path.
     *
     * @param string $file
     * @return string
     * @throws AdminFileException
     */
    protected function validateFilename(string $file): string
    {
        $localFiles = APP_ROOT . '/public/local';

        $realpath = realpath($localFiles . '/' . $file);
        if (!is_string($realpath)) {
            throw new AdminFileException('File does not exit');
        }
        if (strpos($realpath, $localFiles) !== 0) {
            throw new AdminFileException('File does not exist inside public/local.');
        }
        return $realpath;
    }

    /**
     * @param string $in
     * @param bool $prefix
     * @return string
     */
    protected function escapePath(string $in, bool $prefix = false): string
    {
        $pieces = explode('/', str_replace('//', '/', $in));
        foreach ($pieces as $i => $v) {
            if ($v === '.' || $v === '..') {
                unset($pieces[$i]);
            }
        }
        if ($prefix) {
            return APP_ROOT . '/public/local/' . trim(implode('/', $pieces), '/');
        }
        return implode('/', $pieces);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function indexPage(RequestInterface $request): ResponseInterface
    {
        return $this->view('admin/custom-index.twig');
    }
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createFile(RequestInterface $request): ResponseInterface
    {
        $get = $this->get($request);
        $filter = new AdminCreateFileFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        $dir = $this->escapePath($get['dir']);

        if ($post) {
            if ($post['path']) {
                $destination = $this->escapePath(
                    $post['path'] . '/' . $post['file'],
                    true
                );
                if (!is_dir($post['path'])) {
                    // Make sure the directory exists
                    mkdir(
                        $this->escapePath($post['path'], true),
                        0777,
                        true
                    );
                }
            } else {
                $destination = $this->escapePath($post['file'], true);
            }
            if (file_put_contents($destination, $post['contents']) !== false) {
                $this->messageOnce('File saved successfully.', 'success');
                return $this->redirect(
                    '/admin/custom/edit?' . http_build_query([
                        'file' => $post['path']
                            ? $post['path'] . '/' . $post['file']
                            : $post['file']
                    ])
                );
            } else {
                $this->messageOnce('Could not write to file ' . $destination, 'error');
            }
        }
        return $this->view('admin/custom-create.twig', ['dir' => $dir]);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editFile(RequestInterface $request): ResponseInterface
    {
        $get = $this->get($request);
        try {
            if (empty($get['file'])) {
                throw new AdminFileException('No file provided.');
            }
            $file = '/' . trim($get['file'], '/');
            $realpath = $this->validateFilename($file);
        } catch (AdminFileException $ex) {
            $this->messageOnce($ex->getMessage(), 'error');
            return $this->redirect('/admin/custom');
        }

        $filter = new AdminEditFileFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $destination = $this->escapePath(
                $post['path']
                    ? $post['path'] . '/' . $post['file']
                    : $post['file'],
                true
            );
            if (file_put_contents($destination, $post['contents']) !== false) {
                $this->messageOnce('File saved successfully.', 'success');
                return $this->redirect(
                    '/admin/custom/edit?' . http_build_query([
                        'file' => $get['file']
                    ])
                );
            } else {
                $this->messageOnce('Could not write to file ' . $destination, 'error');
            }
        }
        $contents = file_get_contents($realpath);

        $pieces = explode('/', trim($file, '/'));
        $filename = array_pop($pieces);
        $dir = implode('/', $pieces);

        return $this->view('admin/custom-edit.twig', [
            'file' => $filename,
            'dir' => $dir,
            'contents' => $contents
        ]);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $action = $routerParams['action'] ?? 'index';
        switch ($action) {
            case 'create':
                return $this->createFile($request);
            case 'edit':
                return $this->editFile($request);
            case 'index':
                return $this->indexPage($request);
            default:
                return $this->redirect('/admin/custom');
        }
    }
}
