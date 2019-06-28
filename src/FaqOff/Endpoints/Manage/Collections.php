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
use Soatok\FaqOff\Splices\EntryCollection;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class Collections
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Collections extends Endpoint
{
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
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        return $this->json($routerParams);
    }
}
