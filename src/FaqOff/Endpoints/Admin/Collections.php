<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Authors;
use Soatok\FaqOff\Splices\Entry;
use Soatok\FaqOff\Splices\EntryCollection;
use Soatok\FaqOff\Splices\Themes;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Collections
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Collections extends Endpoint
{
    use MessageOnceTrait;

    /** @var Authors $authors */
    private $authors;

    /** @var EntryCollection $collections */
    private $collections;

    /** @var Entry $entries */
    private $entries;

    /** @var Themes $themes */
    private $themes;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authors = $this->splice('Authors');
        $this->collections = $this->splice('EntryCollection');
        $this->entries = $this->splice('Entry');
        $this->themes = $this->splice('Themes');
    }

    /**
     * @param RequestInterface $request
     * @param int $id
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editCollection(RequestInterface $request, int $id): ResponseInterface
    {
        try {
            $collection = $this->collections->getById($id);
        } catch (CollectionNotFoundException $ex) {
            $this->messageOnce($ex->getMessage(), 'error');
            return $this->redirect('/admin/collections');
        }

        $post = $this->post($request);
        if ($post) {
            if (empty($post['author'])) {
                $this->messageOnce('No author specified.', 'error');
            } else{
                if ($collection['author'] !== $post['author']) {
                    if ($collection['url'] === $post['url']) {
                        // Author changed. Regenerate URL.
                        $post['url'] = '';
                    }
                }
                if ($this->collections->updateAsAdmin($id, $post)) {
                    $this->messageOnce('Collection updated successfully.', 'success');
                    return $this->redirect('/admin/collection/' . $id);
                }
            }
        }
        return $this->view('admin/collection-edit.twig', [
            'themes' => $this->themes->getAllThemes(),
            'authors' => $this->authors->listAll(),
            'collection' => $collection
        ]);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listCollections(RequestInterface $request): ResponseInterface
    {
        return $this->view('admin/collections-index.twig', [
            'collections' => $this->collections->getAll()
        ]);
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
        $id = $routerParams['collection'] ?? null;
        if ($id) {
            return $this->editCollection($request, (int) $id);
        }
        return $this->listCollections($request);
    }
}
