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
use Soatok\FaqOff\Splices\Authors;
use Soatok\FaqOff\Splices\Entry;
use Soatok\FaqOff\Splices\EntryCollection;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Entries
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Entries extends Endpoint
{
    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    /** @var Entry $entries */
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
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }
}
