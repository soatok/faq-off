<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\AdminEditSettingsFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Settings
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Settings extends Endpoint
{
    use MessageOnceTrait;

    private $editable = [
        'admins.json',
        'anthrokit.php',
        'settings.php'
    ];

    /**
     * @param RequestInterface $request
     * @param string $file
     * @return ResponseInterface
     * @throws ContainerException
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editFile(
        RequestInterface $request,
        string $file
    ): ResponseInterface {
        $filter = new AdminEditSettingsFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $written = file_put_contents(
                APP_ROOT . '/local/' . $file,
                $post['contents']
            );
            if (!is_bool($written)) {
                $this->messageOnce('File saved successfully!', 'success');
                return $this->redirect(
                    '/admin/settings/edit?' .
                    http_build_query(['file' => $file])
                );
            } else {
                $contents = $post['contents'];
                $this->messageOnce('File could not be saved. Please check its permissions.', 'error');
            }
        } else {
            if (!file_exists(APP_ROOT . '/local/' . $file)) {
                touch(APP_ROOT . '/local/' . $file);
            }
            $contents = file_get_contents(APP_ROOT . '/local/' . $file);
        }
        if (empty($contents)) {
            $contents = '';
        }
        return $this->view(
            'admin/settings-edit.twig',
            [
                'file' => $file,
                'contents' => $contents
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws ContainerException
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $which = $routerParams['which'] ?? '';
        switch ($which) {
            case 'edit':
                $file = $_GET['file'] ?? '';
                if ($file) {
                    if (in_array($file, $this->editable, true)) {
                        return $this->editFile($request, $file);
                    }
                }
                return $this->redirect('/admin/settings');
            case '':
                return $this->view('admin/settings-index.twig');
            default:
                return $this->redirect('/admin/settings');
        }
    }
}
