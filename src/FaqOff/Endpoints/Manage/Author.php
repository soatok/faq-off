<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Slim\Http\StatusCode;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\CreateCollectionFilter;
use Soatok\FaqOff\Splices\Authors;
use Soatok\FaqOff\Splices\EntryCollection;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Author
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Author extends Endpoint
{
    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
    }

    /**
     * @param int $authorId
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function collections(
        int $authorId,
        RequestInterface $request
    ): ResponseInterface {
        return $this->json([__METHOD__, $authorId]);
    }

    /**
     * @param RequestInterface $request
     * @param array $routerParams
     * @return ResponseInterface
     */
    protected function createAuthor(
        RequestInterface $request,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }

    /**
     * @param int $authorId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createCollection(
        int $authorId,
        RequestInterface $request
    ): ResponseInterface {
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            return $this->redirect('/manage/authors', StatusCode::HTTP_FORBIDDEN);
        }
        $errors = [];
        $filter = new CreateCollectionFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            try {
                $collectionId = $this->collections->create($authorId, $post);
                if ($collectionId) {
                    return $this->redirect('/manage/collection/' . $collectionId);
                } else {
                    $errors []= 'An unknown error occurred when creating a new collection.';
                }
            } catch (\Throwable $ex) {
                $errors []= $ex->getMessage();
            }
        }
        return $this->view(
            'manage/collection-create.twig',
            [
                'author' => $this->authors->getById($authorId),
                'errors' => $errors,
                'post' => $post
            ]
        );
    }

    /**
     * @param int $authorId
     * @param RequestInterface $request
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editProfile(
        int $authorId,
        RequestInterface $request
    ): ResponseInterface {
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            return $this->redirect('/manage/authors', StatusCode::HTTP_FORBIDDEN);
        }
        $errors = [];
        $post = $this->post($request);
        if ($post) {

        }
        return $this->view(
            'manage/author-edit.twig',
            [
                'author' => $this->authors->getById($authorId),
                'collections' => $this->collections->getAllByAuthor($authorId),
                'errors' => $errors,
                'post' => $post
            ]
        );
    }

    /**
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function homePage(): ResponseInterface
    {
        return $this->view(
            'manage/authors-index.twig',
            [
                'authors' =>
                    $this->authors->getByAccount($_SESSION['account_id'])
            ]
        );
    }

    /**
     *
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     *
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
        if (isset($routerParams['id'])) {
            if (!isset($routerParams['action'])) {
                return $this->editProfile((int) $routerParams['id'], $request);
            }
            switch ($routerParams['action']) {
                case 'collection':
                case 'collections':
                    $sub = $routerParams['sub'] ?? '';
                    if ($sub === 'create') {
                        return $this->createCollection((int) $routerParams['id'], $request);
                    }
                    return $this->collections((int) $routerParams['id'], $request);
            }
        } elseif (isset($routerParams['action'])) {
            if ($routerParams['action'] === 'create') {
                return $this->createAuthor($request, $routerParams);
            }
        }
        return $this->homePage();
    }
}
