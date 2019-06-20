<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Soatok\AnthroKit\Endpoint;
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
