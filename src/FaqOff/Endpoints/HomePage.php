<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\FrontendEndpoint;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\{
    Authors,
    Notices
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class HomePage
 * @package Soatok\FaqOff\Endpoints
 */
class HomePage extends FrontendEndpoint
{
    use MessageOnceTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var \Soatok\FaqOff\Splices\EntryCollection $collections */
    private $collections;

    /** @var \Soatok\FaqOff\Splices\Entry $entries */
    private $entries;

    /** @var Notices $notices */
    private $notices;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->notices = $this->splice('Notices');
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function index(): ResponseInterface
    {
        $config = $this->container['settings'];
        return $this->view('index.twig', [
            'popular_collections' => $this->collections->getMostPopular(),
            'popular_entries' => $this->entries->getMostPopular(),
            'notices' => $this->notices->getRecent(
                (int) ($config['front-news-limit'])
            )
        ]);
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
        switch ($request->getUri()->getPath()) {
            case '/':
            case '':
                return $this->index();
        }
        return $this->redirect('/');
    }
}