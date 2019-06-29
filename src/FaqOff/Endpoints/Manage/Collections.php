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
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;
use Soatok\FaqOff\Filter\EditCollectionFilter;
use Soatok\FaqOff\Splices\{
    Authors,
    EntryCollection
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Collections
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Collections extends Endpoint
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
     * @param RequestInterface $request
     * @param int $collectionId
     * @param array $routerParams
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws CollectionNotFoundException
     */
    protected function editCollection(
        RequestInterface $request,
        int $collectionId,
        array $routerParams = []
    ): ResponseInterface {
        $authorId = $this->collections->getCollectionAuthorId($collectionId);
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            return $this->redirect('/manage/authors', StatusCode::HTTP_FORBIDDEN);
        }
        $errors = [];
        $filter = new EditCollectionFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->collections->update($collectionId, $post)) {
                // Success
                return $this->redirect('/manage/collections');
            } else {
                $errors[] = "An unknown error occurred trying to update the collection";
            }
        }
        $collection = $this->collections->getById($collectionId);
        return $this->view(
            'manage/collection-edit.twig',
            [
                'collection' => $collection,
                'post' => $post + $collection
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
    protected function homePage(): ResponseInterface
    {
        return $this->view(
            'manage/collections-index.twig',
            [
                'collections' =>
                    $this->collections->getByAccount($_SESSION['account_id'])
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws CollectionNotFoundException
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        if (isset($routerParams['id'])) {
            $action = $routerParams['action'] ?? null;
            switch ($action) {
                default:
                    return $this->editCollection(
                        $request,
                        (int) $routerParams['id'],
                        $routerParams
                    );
            }
        }
        return $this->homePage();
    }
}
