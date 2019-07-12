<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Splices\Accounts as AccountSplice;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class InviteTree
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class InviteTree extends Endpoint
{
    /** @var AccountSplice $accounts  */
    protected $accounts;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->accounts = $this->splice('Accounts');
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
        return $this->view(
            'admin/invite-tree.twig',
            [
                'tree' => $this->accounts->getInviteTree()
            ]
        );
    }
}
