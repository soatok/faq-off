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
use Slim\Http\Response;
use Soatok\FaqOff\FrontendEndpoint;
use Soatok\FaqOff\MessageOnceTrait;
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
 * Class Author
 * @package Soatok\FaqOff\Endpoints
 */
class Author extends FrontendEndpoint
{
    use MessageOnceTrait;

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
     * @return Response
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listing(RequestInterface $request): Response
    {
        /** @var Authors $authors */
        $authors = $this->splice('Authors');
        return $this->view('author-listing.twig', [
            'authors' => $authors->listAllScreenNames()
        ]);
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
    public function authorPage(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $author = $this->authors->getByScreenName($routerParams['author']);
        if (empty($author)) {
            $this->messageOnce('Author does not exist', 'error');
            return $this->redirect('/');
        }

        /** @var CommonMarkConverter $converter */
        $converter = $this->container['markdown'];

        /** @var \HTMLPurifier $purifier */
        $purifier = $this->container['purifier'];
        $author['biography'] = $purifier->purify(
            $converter->convertToHtml(
                $author['biography'] ?? '*No biography specified*'
            )
        );

        return $this->view('author.twig', [
            'author' => $author,
            'collections' => $this->collections->getAllByAuthor((int) $author['authorid'])
        ]);
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
            return $this->listing($request);
        }
        return $this->authorPage($request, $response, $routerParams);
    }
}
