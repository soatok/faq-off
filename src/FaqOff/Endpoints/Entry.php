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
use Slim\Http\Request;
use Soatok\AnthroKit\Endpoint;
use Soatok\AnthroKit\Privacy;
use Soatok\DholeCrypto\Exceptions\CryptoException;
use Soatok\FaqOff\{
    Filter\FrontQuestionFilter,
    FrontAccountInfoTrait,
    MessageOnceTrait
};
use Soatok\FaqOff\Splices\Authors;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Entry
 * @package Soatok\FaqOff\Endpoints
 */
class Entry extends Endpoint
{
    use MessageOnceTrait;
    use FrontAccountInfoTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var \Soatok\FaqOff\Splices\EntryCollection $collections */
    private $collections;

    /** @var \Soatok\FaqOff\Splices\Entry $entries */
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
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws CryptoException
     * @throws \SodiumException
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
        if (empty($routerParams['entry'])) {
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
        $collectionId = (int) $collection['collectionid'];

        $entry = $this->entries->getByCollectionAndUrl(
            $collectionId,
            $routerParams['entry']
        );

        if (!$entry) {
            $this->messageOnce('Entry does not exist.', 'error');
            return $this->redirect(
                '/@' . $author['screenname'] . '/' . $collection['url']
            );
        }
        $questionsAllowed = false;
        if ($this->isLoggedIn()) {
            $questionsAllowed = !empty($entry['allow_questions']);
        }
        if ($questionsAllowed) {
            // We only process this if the user is allowed to post a question:
            $filter = new FrontQuestionFilter();
            $post = $this->post($request, self::TYPE_FORM, $filter);
            if ($post) {
                return $this->addQuestion(
                    $post,
                    'entry',
                    $entry,
                    '/@' . $author['screenname'] .
                        '/' . $collection['url'] .
                        '/' . $routerParams['entry']
                );
            }
        }
        $this->setTwigVar('allow_questions', $questionsAllowed);

        /** @var CommonMarkConverter $converter */
        $converter = $this->container['markdown'];

        /** @var \HTMLPurifier $purifier */
        $purifier = $this->container['purifier'];

        $entry['contents'] = $purifier->purify(
            $converter->convertToHtml(
                $entry['contents'] ?? ''
            )
        );
        $this->setTwigVar('theme_id', $collection['theme'] ?? null);
        if (!($request instanceof Request)) {
            throw new \TypeError();
        }

        if ($this->container->get('settings')['aggregate-stats']) {
            $privacy = new Privacy();
            $this->entries->countHit(
                $privacy->anonymize($request),
                $entry['entryid'],
                $privacy->maskInteger((int)($_SESSION['account_id'] ?? 0))
            );
        }

        return $this->view(
            'entry.twig',
            [
                'author' => $author,
                'collection' => $collection,
                'entry' => $entry,
                'pageTitle' => $entry['title']
            ]
        );
    }
}
