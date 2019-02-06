<?php
namespace Soatok\FaqOff;

use Slim\Container;

trait HandlerContainerTrait
{
    /** @var Container $c */
    protected $container;

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
