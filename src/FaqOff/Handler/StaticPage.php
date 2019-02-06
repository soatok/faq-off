<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\FaqOff\HandlerContainerTrait;
use Soatok\FaqOff\HandlerInterface;
use Soatok\FaqOff\Utility;

/**
 * Class StaticPage
 * @package Soatok\FaqOff\Handler
 */
class StaticPage implements HandlerInterface
{
    use HandlerContainerTrait;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->container->get('twig');
        $body = $twig->render('index.twig');
        return Utility::createResponse($body);
    }

    public function __invoke(RequestInterface $request): ResponseInterface
    {
        switch ($request->getUri()->getPath()) {
            case '/':
            case '':
                return $this->index($request);
        }
        return $this->errorPage(404);
    }
}
