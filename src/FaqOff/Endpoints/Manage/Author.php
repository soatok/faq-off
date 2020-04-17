<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\{
    CreateAuthorFilter,
    CreateCollectionFilter,
    EditAuthorFilter
};
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\{
    Authors,
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
 * Class Author
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Author extends Endpoint
{
    const QUESTION_TYPE = 'author';
    use MessageOnceTrait;
    use QuestionableTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    /** @var Questions $questions */
    private $questions;

    /** @var Themes $themes */
    private $themes;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->questions = $this->splice('Questions');
        $this->themes = $this->splice('Themes');
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
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function createAuthor(RequestInterface $request): ResponseInterface
    {
        $filter = new CreateAuthorFilter();
        $errors = [];
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            try {
                if ($this->authors->create(
                    $post['name'],
                    $_SESSION['account_id'],
                    $post['biography'] ?? ''
                )) {
                    return $this->redirect('/manage/authors');
                } else {
                    $errors[] = 'An unknown error occurred creating a new author.';
                }
            } catch (\Exception $ex) {
                $errors []= $ex->getMessage();
            }
        }
        return $this->view(
            'manage/author-create.twig',
            [
                'errors' => $errors,
                'post' => $post
            ]
        );
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
            $this->messageOnce('You do not have access to this author.', 'error');
            return $this->redirect('/manage/authors');
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
                'themes' => $this->themes->getAllThemes(),
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
            $this->messageOnce('You do not have access to this author.', 'error');
            return $this->redirect('/manage/authors');
        }
        $filter = new EditAuthorFilter();
        $errors = [];
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->authors->updateBiography($authorId, $post['biography'])) {
                $this->messageOnce('Author biography updated successfully');
                return $this->redirect('/manage/author/' . $authorId);
            } else {
                $errors []= 'An unknown error has occurred updating your biography.';
            }
        }
        $author = $this->authors->getById($authorId);
        $author['question_count'] = $this->questions->countForAuthor(
            (int) $author['authorid']
        );
        return $this->view(
            'manage/author-edit.twig',
            [
                'is_owner' => $author['ownerid'] === $_SESSION['account_id'],
                'contributors' => $this->authors->listContributors($authorId),
                'author' => $author,
                'collections' => $this->collections->getAllByAuthor($authorId),
                'errors' => $errors,
                'post' => $post + $author
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
        // question_count
        $authors = $this->authors->getByAccount($_SESSION['account_id']);
        foreach ($authors as $i => $author) {
            $authors[$i]['question_count'] = $this->questions->countForAuthor(
                (int) $author['authorid']
            );
        }
        return $this->view(
            'manage/authors-index.twig',
            [
                'authors' => $authors
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
                        return $this->createCollection(
                            (int) $routerParams['id'],
                            $request
                        );
                    }
                    return $this->collections(
                        (int) $routerParams['id'],
                        $request
                    );
                case 'inbox':
                    return $this->questionQueue(
                        $request,
                        (int) $routerParams['id'],
                        $routerParams
                    );
            }
        } elseif (isset($routerParams['action'])) {
            if ($routerParams['action'] === 'create') {
                return $this->createAuthor($request);
            }
        }
        return $this->homePage();
    }
}
