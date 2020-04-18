<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Filter\AdminEditAuthorFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\{
    Authors as AuthorSplice,
    EntryCollection
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Authors
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Authors extends BackendEndpoint
{
    use MessageOnceTrait;

    /** @var AuthorSplice $authors */
    protected $authors;

    /** @var EntryCollection $collections */
    protected $collections;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
    }

    /**
     * @param RequestInterface $request
     * @param int $authorId
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editAuthor(RequestInterface $request, int $authorId): ResponseInterface
    {
        $filter = new AdminEditAuthorFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->authors->updateAuthorByAdmin($authorId, $post)) {
                $this->messageOnce('Author updated successfully.', 'success');
                return $this->redirect('/admin/author/edit/' . $authorId);
            }
        }
        return $this->view(
            'admin/authors-edit.twig',
            [
                'author' => $this->authors->getById($authorId),
                'contributors' => $this->authors->getContributorIds($authorId)
            ]
        );
    }

    /**
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listAuthors(): ResponseInterface
    {
        return $this->view(
            'admin/authors-list.twig',
            [
                'authors' => $this->authors->listAll(true)
            ]
        );
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
        $action = $routerParams['action'] ?? null;
        switch ($action) {
            case 'edit':
                return $this->editAuthor($request, (int) $routerParams['id']);
            case '':
                return $this->listAuthors();
            default:
                return $this->redirect('/admin/authors');
        }
    }
}
