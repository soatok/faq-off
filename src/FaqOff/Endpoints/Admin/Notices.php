<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\AdminCreateNoticeFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Notices
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Notices extends Endpoint
{
    use MessageOnceTrait;

    /** @var \Soatok\FaqOff\Splices\Notices $notices */
    private $notices;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->notices = $this->splice('Notices');
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createNotice(RequestInterface $request): ResponseInterface
    {
        $filter = new AdminCreateNoticeFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            $id = $this->notices->create(
                $post['title'],
                $post['contents'],
                (int) $_SESSION['account_id']
            );
            if ($id) {
                $this->messageOnce('Notice posted to the public.', 'success');
                return $this->redirect('/admin/notices/' . $id);
            }
        }
        return $this->view('admin/notice-create.twig', [
            'notices' => $this->notices->getAll()
        ]);
    }

    /**
     * @param int $id
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function editNotice(int $id, RequestInterface $request): ResponseInterface
    {
        $filter = new AdminCreateNoticeFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->notices->update(
                $id,
                $post['title'],
                $post['contents'],
                (int) $_SESSION['account_id']
            )) {
                $this->messageOnce('Notice updated successfully.', 'success');
                return $this->redirect('/admin/notices/' . $id);
            }
        }
        return $this->view('admin/notice-edit.twig', [
            'notice' => $this->notices->getById($id)
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
        $id = $routerParams['id'] ?? null;
        if ($id) {
            return $this->editNotice((int) $id, $request);
        }
        return $this->createNotice($request);
    }
}
