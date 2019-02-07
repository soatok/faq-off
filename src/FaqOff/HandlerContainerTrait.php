<?php
namespace Soatok\FaqOff;

use ParagonIE\EasyDB\EasyDB;
use Slim\Container;
use Slim\Http\Response;

/**
 * Trait HandlerContainerTrait
 * @package Soatok\FaqOff
 */
trait HandlerContainerTrait
{
    /** @var Container $c */
    protected $container;

    /**
     * @return EasyDB
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getDatabase(): EasyDB
    {
        return $this->container->get('database');
    }

    /**
     * @param int $status
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
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
