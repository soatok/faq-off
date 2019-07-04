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
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\MessageOnceTrait;
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

        /** @var CommonMarkConverter $converter */
        $converter = $this->container['markdown'];

        /** @var \HTMLPurifier $purifier */
        $purifier = $this->container['purifier'];

        $entry['contents'] = $purifier->purify(
            $converter->convertToHtml(
                $entry['contents'] ?? ''
            )
        );

        return $this->view(
            'entry.twig',
            [
                'author' => $author,
                'collection' => $collection,
                'entry' => $entry
            ]
        );
    }
}
