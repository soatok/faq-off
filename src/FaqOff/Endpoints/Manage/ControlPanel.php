<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\Splices\{
    Authors,
    EntryCollection
};
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};

/**
 * Class ControlPanel
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class ControlPanel extends BackendEndpoint
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
            'manage/index.twig',
            [
                'public_id' =>
                    $this->accounts->getPublicId($_SESSION['account_id']),
                'authors' =>
                    $this->authors->getByAccount($_SESSION['account_id']),
                'collections' =>
                    $this->collections->getByAccount($_SESSION['account_id'])
            ]
        );
    }
}
