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
 * Class StaticPage
 * @package Soatok\FaqOff\Handler
 */
class StaticPage extends Endpoint
{
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
        return $this->view('index.twig');
    }

    /**
     * @param int $status
     *
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function errorPage(int $status = 404): ResponseInterface
    {
        return $this->view('error' . $status . '.twig', [], $status);
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
        return $this->errorPage(404);
    }
}
