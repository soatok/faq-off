<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Filter\{
    CreateThemeFilter,
    EditThemeFilter
};
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Themes as ThemeSplice;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Themes
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Themes extends BackendEndpoint
{
    use MessageOnceTrait;

    /** @var ThemeSplice $themes */
    protected $themes;

    /**
     * Themes constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->themes = $this->splice('Themes');
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createTheme(RequestInterface $request): ResponseInterface
    {
        $filter = new CreateThemeFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $id = $this->themes->createTheme(
                $post['name'],
                $post['description'],
                [
                    'css_files' => $post['css_files'],
                    'js_files' => $post['js_files'],
                    'twig_vars' => json_decode($post['twig_vars'], true)
                ]
            );
            if ($id) {
                $this->messageOnce('Theme successfully created!', 'success');
                return $this->redirect('/admin/theme/edit/' . $id);
            }
        }
        return $this->view('admin/themes-create.twig');
    }

    /**
     * @param RequestInterface $request
     * @param int $id
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editTheme(RequestInterface $request, int $id): ResponseInterface
    {
        $filter = new EditThemeFilter();
        $theme = $this->themes->getById($id);
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $success = $this->themes->updateTheme(
                $id,
                $post['name'],
                $post['description'],
                [
                    'css_files' => $post['css_files'],
                    'js_files' => $post['js_files'],
                    'twig_vars' => json_decode($post['twig_vars'], true)
                ]
            );
            if ($success) {
                $this->messageOnce('Theme successfully updated!', 'success');
                return $this->redirect('/admin/theme/edit/' . $id);
            }
        }
        return $this->view('admin/themes-edit.twig', ['theme' => $theme]);
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
            case 'edit':
                if (empty($routerParams['id'])) {
                    return $this->redirect('/admin/themes');
                }
                return $this->editTheme($request, (int) $routerParams['id']);
            case 'create':
                return $this->createTheme($request);
            case 'index':
                $themes = $this->themes->getAllThemes();
                return $this->view('admin/themes-index.twig', ['themes' => $themes]);
            default:
                return $this->redirect('/admin/themes');
        }
    }
}
