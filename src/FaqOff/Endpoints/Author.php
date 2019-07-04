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
use Soatok\FaqOff\Splices\EntryCollection;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Author
 * @package Soatok\FaqOff\Endpoints
 */
class Author extends Endpoint
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
}
