<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;
use Soatok\FaqOff\Filter\EditCollectionFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\{
    Authors,
    Entry,
    EntryCollection,
    Questions,
    Themes
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
class Collections extends BackendEndpoint
{
    const QUESTION_TYPE = 'collection';

    use MessageOnceTrait;
    use QuestionableTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    /** @var Entry $entries */
    private $entries;

    /** @var Questions */
    private $questions;

    /** @var Themes $themes */
    private $themes;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->questions = $this->splice('Questions');
        $this->themes = $this->splice('Themes');
    }

    /**
     * @param RequestInterface $request
     * @param int $collectionId
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
        int $collectionId
    ): ResponseInterface {
        try {
            $authorId = $this->collections->getCollectionAuthorId($collectionId);
        } catch (CollectionNotFoundException $ex) {
            $this->messageOnce('This collection does not exist.', 'error');
            return $this->redirect('/manage/collections');
        }

        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            $this->messageOnce('You do not have access to collections belonging to this author.', 'error');
            return $this->redirect('/manage/authors');
        }
        $errors = [];
        $filter = new EditCollectionFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->collections->update($collectionId, $post)) {
                // Success
                $this->messageOnce('Collection updated successfully', 'success');
                return $this->redirect('/manage/collection/' . $collectionId);
            } else {
                $errors[] = "An unknown error occurred trying to update the collection";
            }
        }
        $collection = $this->collections->getById($collectionId);
        $collection['question_count'] = $this->questions->countForCollection($collectionId);
        return $this->view(
            'manage/collection-edit.twig',
            [
                'themes' => $this->themes->getAllThemes(),
                'collection' => $collection,
                'post' => $post + $collection
            ]
        );
    }

    /**
     * @param int $collectionId
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listEntries(int $collectionId): ResponseInterface
    {
        $authorId = $this->collections->getCollectionAuthorId($collectionId);
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            $this->messageOnce('You do not have access to collections belonging to this author.', 'error');
            return $this->redirect('/manage/authors');
        }
        $collection = $this->collections->getById($collectionId);
        $collection['question_count'] = $this->questions->countForCollection($collectionId);
        $entries = $this->entries->listByCollectionId($collectionId);
        foreach ($entries as $i => $entry) {
            $entries[$i]['question_count'] = $this->questions->countForEntry(
                (int) $entry['entryid']
            );
        }
        return $this->view(
            'manage/collection-entries.twig',
            [
                'collection' => $collection,
                'entries' => $entries
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
        $collections = $this->collections->getByAccount($_SESSION['account_id']);
        foreach ($collections as $i => $collection) {
            $collections[$i]['question_count'] = $this->questions->countForCollection(
                (int) $collection['entryid']
            );
        }
        return $this->view(
            'manage/collections-index.twig',
            [
                'collections' => $collections
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
                case 'entries':
                    return $this->listEntries((int) $routerParams['id']);
                case 'inbox':
                    return $this->questionQueue(
                        $request,
                        (int) $routerParams['id'],
                        $routerParams
                    );
                default:
                    return $this->editCollection($request, (int) $routerParams['id']);
            }
        }
        return $this->homePage();
    }
}
