<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Splices\{
    Authors,
    Entry,
    EntryCollection,
    Questions as QuestionsSplice
};
use Soatok\FaqOff\Filter\AdminEditQuestionFilter;
use Soatok\FaqOff\MessageOnceTrait;

/**
 * Class Questions
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Questions extends BackendEndpoint
{
    use MessageOnceTrait;

    /** @var Authors $authors */
    protected $authors;

    /** @var EntryCollection $collection */
    protected $collection;

    /** @var Entry $entries */
    protected $entries;

    /** @var QuestionsSplice $questions */
    protected $questions;

    /**
     * Questions constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collection = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->questions = $this->splice('Questions');
    }

    /**
     * @param RequestInterface $request
     * @param int $questionId
     * @return ResponseInterface
     */
    protected function editQuestion(
        RequestInterface $request,
        int $questionId
    ): ResponseInterface {
        $question = $this->questions->getQuestion($questionId);
        if (empty($question)) {
            $this->messageOnce(
                "Question {$questionId} not found!",
                'error'
            );
            return $this->redirect('/admin/questions');
        }
        $filter = new AdminEditQuestionFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->questions->updateByAdmin($questionId, $post)) {
                $this->messageOnce('Updated successfully!');
            }
            return $this->redirect('/admin/questions/edit/' . $questionId);
        }
        return $this->view('admin/questions-edit.twig', [
            'collections' => $this->collection->getAll(),
            'entries' => $this->entries->getAll(),
            'question' => $question
        ]);
    }

    /**
     * @param string $type
     * @param int $typeId
     * @return ResponseInterface
     */
    protected function listForType(string $type, int $typeId): ResponseInterface
    {
        switch ($type) {
            case 'account':
                $list = $this->questions->getFromAccount($typeId, true);
                $item = $this->accounts->getInfoByAccountId($typeId);
                $metadata = [
                    'title' => $item['public_id']
                ];
                break;
            case 'author':
                $list = $this->questions->getForAuthor($typeId, true);
                $item = $this->authors->getById($typeId);
                $metadata = [
                    'title' => $item['screenname']
                ];
                break;
            case 'collection':
                $list = $this->questions->getForCollection($typeId, true);
                $item = $this->collection->getById($typeId);
                $metadata = [
                    'title' => $item['title']
                ];
                break;
            case 'entry':
                $list = $this->questions->getForEntry($typeId, true);
                $item = $this->entries->getById($typeId);
                $metadata = [
                    'title' => $item['title']
                ];
                break;
            default:
                return $this->redirect('/admin/questions');
        }
        return $this->view('admin/questions-list.twig', [
            'qtype' => $type,
            'questionlist' => $list,
            'qmeta' => $metadata
        ]);
    }

    /**
     * @param string $type
     * @return ResponseInterface
     */
    protected function listTypes(string $type): ResponseInterface
    {
        switch ($type) {
            case 'account':
                $list = $this->accounts->listForAdminQuestionIndex();
                break;
            case 'author':
                $list = $this->authors->listForAdminQuestionIndex();
                break;
            case 'collection':
                $list = $this->collection->listForAdminQuestionIndex();
                break;
            case 'entry':
                $list = $this->entries->listForAdminQuestionIndex();
                break;
            default:
                return $this->redirect('/admin/questions');
        }
        return $this->view('admin/questions-typelist.twig', [
            'qtype' => $type,
            'typelist' => $list
        ]);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        if (!empty($routerParams['id'])) {
            return $this->editQuestion($request, (int) $routerParams['id']);
        }
        if (!empty($routerParams['type'])) {
            if (!empty($routerParams['type_id'])) {
                return $this->listForType(
                    $routerParams['type'],
                    (int) $routerParams['type_id']
                );
            }
            return $this->listTypes($routerParams['type']);
        }
        return $this->view('admin/questions-index.twig');
    }
}
