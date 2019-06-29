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
use Soatok\FaqOff\Filter\CreateEntryFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Authors;
use Soatok\FaqOff\Splices\Entry;
use Soatok\FaqOff\Splices\EntryCollection;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Entries
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Entries extends Endpoint
{
    use MessageOnceTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    /** @var Entry $entries */
    private $entries;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
    }

    /**
     * @param int $collectionId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createEntry(
        int $collectionId,
        RequestInterface $request
    ): ResponseInterface {
        $filter = new CreateEntryFilter();
        $errors = [];
        $post = $this->post($request);
        return $this->view(
            'manage/entry-create.twig',
            [
                'collection' => $this->collections->getById($collectionId),
                'post' => $post
            ]
        );

    }

    /**
     * @param int $collectionId
     * @param int $entryId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editEntry(
        int $collectionId,
        int $entryId,
        RequestInterface $request
    ): ResponseInterface {
        $errors = [];
        $post = $this->post($request);
        $entry = $this->entries->getById($entryId);
        return $this->view(
            'manage/entry-edit.twig',
            [
                'collection' => $this->collections->getById($collectionId),
                'entry' => $entry,
                'post' => $post + $entry
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $collectionId = (int) $routerParams['collection'];
        $authorId = $this->collections->getCollectionAuthorId($collectionId);
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            $this->messageOnce('You do not have access to collections belonging to this author.', 'error');
            return $this->redirect('/manage/authors');
        }

        if (!empty($routerParams['create'])) {
            return $this->createEntry($collectionId, $request);
        }
        $entryId = (int) $routerParams['entry'];
        if (!$this->entries->belongsTo($collectionId, $entryId)) {
            $this->messageOnce('This entry does not belong to this collection.', 'error');
            return $this->redirect(
                '/manage/collection/' . $collectionId . '/entries'
            );
        }
        $action = $routerParams['action'] ?? '';
        switch ($action) {
            default:
                return $this->editEntry($collectionId, $entryId, $request);
        }
    }
}
