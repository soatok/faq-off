<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;
use Soatok\FaqOff\Filter\{
    CreateEntryFilter,
    QuestionIdFilter
};
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\{
    Authors,
    Entry,
    EntryCollection,
    Questions
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};
use Soatok\FaqOff\Utility;

/**
 * Class Entries
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Entries extends BackendEndpoint
{
    const QUESTION_TYPE = 'entry';
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

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->questions = $this->splice('Questions');
    }

    /**
     * Grab a question.
     *
     * @param RequestInterface $request
     * @param int $authorId
     * @return array
     */
    protected function getQuestion(
        RequestInterface $request,
        int $authorId
    ): array {
        $filter = new QuestionIdFilter();
        $get = Utility::getGetVars($request, $filter);
        $questionId = $get['question'] ?? 0;
        if (empty($questionId)) {
            return [];
        }
        $row = $this->questions->getQuestionAuthorCheck((int) $questionId, $authorId);
        if (empty($row)) {
            return [];
        }
        $row['questionid'] = (int) $row['questionid'];
        return $row;
    }

    /**
     * @param int $collectionId
     * @param int $authorId
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createEntry(
        int $collectionId,
        int $authorId,
        RequestInterface $request
    ): ResponseInterface {
        $this->cspBuilder->setSelfAllowed('style-src', true);
        $filter = new CreateEntryFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        $question = $this->getQuestion($request, $authorId);
        if ($post) {
            $newEntryId = $this->entries->create(
                $collectionId,
                $authorId,
                $post['title'] ?? '',
                $post['contents'] ?? '',
                $post['attach-to'] ?? [],
                $post['index-me'],
                $post['question_box'],
                $question['questionid'] ?? null,
                !empty($post['opengraph_image_url'])
                    ? $post['opengraph_image_url']
                    : null
            );
            if ($newEntryId) {
                $this->messageOnce('Entry created successfully', 'success');
                return $this->redirect(
                    '/manage/collection/' . $collectionId . '/entry/' . $newEntryId
                );
            }
        }
        $collection = $this->collections->getById($collectionId);
        $collection['question_count'] = $this->questions->countForCollection($collectionId);
        return $this->view(
            'manage/entry-create.twig',
            [
                'collection' => $collection,
                'post' => $post,
                'question' => $question
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
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editEntry(
        int $collectionId,
        int $entryId,
        RequestInterface $request
    ): ResponseInterface {
        $this->cspBuilder->setSelfAllowed('style-src', true);
        $post = $this->post($request);
        if ($post) {
            if ($this->entries->update($entryId, $post)) {
                $this->messageOnce('Update sucessful', 'success');
                return $this->redirect(
                    '/manage/collection/' . $collectionId . '/entry/' . $entryId
                );
            }
        }
        $entry = $this->entries->getById($entryId);
        $entry['options']['follow-up'] = $this->entries->getFollowUps(
            $entry['options']['follow-up'] ?? []
        );
        $entry['question_count'] = $this->questions->countForEntry($entryId);
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
     *
     * @throws CollectionNotFoundException
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
        $collectionId = (int) $routerParams['collection'];
        $authorId = $this->collections->getCollectionAuthorId($collectionId);
        if (!$this->authors->accountHasAccess($authorId, $_SESSION['account_id'])) {
            $this->messageOnce('You do not have access to collections belonging to this author.', 'error');
            return $this->redirect('/manage/authors');
        }

        if (!empty($routerParams['create'])) {
            return $this->createEntry($collectionId, $authorId, $request);
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
            case 'inbox':
                return $this->questionQueue(
                    $request,
                    (int) $routerParams['entry'],
                    $routerParams
                );
            default:
                return $this->editEntry($collectionId, $entryId, $request);
        }
    }
}
