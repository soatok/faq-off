<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Authors;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class HomePage
 * @package Soatok\FaqOff\Endpoints
 */
class HomePage extends Endpoint
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
     *
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function index(RequestInterface $request): ResponseInterface
    {
        return $this->view('index.twig', [
            'popular_collections' => $this->collections->getMostPopular(),
            'popular_entries' => $this->entries->getMostPopular(),
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
                return $this->index($request);
        }
        return $this->redirect('/');
    }
}