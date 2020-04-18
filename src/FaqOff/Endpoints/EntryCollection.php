<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Interop\Container\Exception\ContainerException;
use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\{
    Filter\FrontQuestionFilter,
    FrontAccountInfoTrait,
    FrontendEndpoint,
    MessageOnceTrait
};
use Soatok\FaqOff\Splices\{
    Authors,
    Entry,
    EntryCollection as Collection
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class EntryCollection
 * @package Soatok\FaqOff\Endpoints
 */
class EntryCollection extends FrontendEndpoint
{
    use MessageOnceTrait;
    use FrontAccountInfoTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var Collection $collections */
    private $collections;

    /** @var Entry $entries */
    private $entries;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->questions = $this->splice('Questions');
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
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        if (empty($routerParams['author'])) {
            return $this->redirect('/');
        }
        if (empty($routerParams['collection'])) {
            return $this->redirect('/');
        }
        $author = $this->authors->getByScreenName($routerParams['author']);
        if (!$author) {
            $this->messageOnce('Author not found.', 'error');
            return $this->redirect('/');
        }

        $authorId = (int) $author['authorid'];
        $collection = $this->collections->getByAuthorAndUrl(
            $authorId,
            $routerParams['collection']
        );
        if (!$collection) {
            $this->messageOnce('Collection does not exist.', 'error');
            return $this->redirect('/@' . $author['screenname']);
        }
        if (!empty($collection['description'])) {
            /** @var CommonMarkConverter $converter */
            $converter = $this->container['markdown'];

            /** @var \HTMLPurifier $purifier */
            $purifier = $this->container['purifier'];
            $collection['description'] = $purifier->purify(
                $converter->convertToHtml(
                    $collection['description'] ?? ''
                )
            );
        }
        $this->setTwigVar('theme_id', $collection['theme'] ?? null);
        $questionsAllowed = false;
        if ($this->isLoggedIn()) {
            $questionsAllowed = !empty($collection['allow_questions']);
        }
        if ($questionsAllowed) {
            // We only process this if the user is allowed to post a question:
            $filter = new FrontQuestionFilter();
            $post = $this->post($request, self::TYPE_FORM, $filter);
            if ($post) {
                return $this->addQuestion(
                    $post,
                    'collection',
                    $collection,
                    '/@' . $author['screenname'] . '/' . $routerParams['collection']
                );
            }
        }
        $this->setTwigVar('allow_questions', $questionsAllowed);

        return $this->view('collection.twig', [
            'collection' => $collection,
            'entries' => $this->entries->listIndexedByCollectionId(
                $collection['collectionid']
            ),
            'pageTitle' => 'Collection ' . $collection['title'] . ' by @' . $author['screenname']
        ]);
    }
}
