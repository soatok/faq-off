<?php
namespace Soatok\FaqOff;

use Slim\Container;
use Slim\Http\Response;

trait HandlerContainerTrait
{
    /** @var Container $c */
    protected $container;

    public function errorPage(int $status = 500): Response
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->container->get('twig');
        $body = $twig->render('error' . $status . '.twig');
        return Utility::createResponse($body);
    }

    /**
     * @param Container $c
     * @return HandlerInterface
     */
    public function setContainer(Container $c): HandlerInterface
    {
        if (!($this instanceof HandlerInterface)) {
            throw new \TypeError('This trait cannot be used except by handlers.');
        }
        $this->container = $c;
        return $this;
    }
}
