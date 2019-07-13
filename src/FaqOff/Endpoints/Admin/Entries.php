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
 * Class Entries
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Entries extends Endpoint
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
     * @param int $collection
     * @param int $entry
     * @return ResponseInterface
     */
    protected function editEntry(
        RequestInterface $request,
        int $collection,
        int $entry
    ): ResponseInterface {
        return $this->json([]);
    }

    /**
     * @param int $collection
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws CollectionNotFoundException
     */
    protected function listEntries(
        int $collection
    ): ResponseInterface {
        $entries = $this->entries->listByCollectionId($collection);
        return $this->view(
            'admin/entries-list.twig',
            [
                'collection' => $this->collections->getById($collection),
                'entries' => $entries
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param int $collection
     * @param int $entryId
     * @param string $extra
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listRecentChanges(
        int $collection,
        int $entryId
    ): ResponseInterface {
        $collection = $this->collections->getById($collection);
        $entry = $this->entries->getById($entryId);
        $changes = $this->entries->listChanges($entryId);

        return $this->view(
            'admin/entries-log-list.twig',
            [
                'collection' => $collection,
                'entry' => $entry,
                'changes' => $changes
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param int $collection
     * @param int $entryId
     * @param int $changeId
     * @return ResponseInterface
     * @throws CollectionNotFoundException
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function viewChangelog(
        RequestInterface $request,
        int $collection,
        int $entryId,
        int $changeId
    ): ResponseInterface {
        $collection = $this->collections->getById($collection);
        $entry = $this->entries->getById($entryId);
        $change = $this->entries->getEntryChange($entryId, $changeId);
        return $this->view(
            'admin/entries-log-view.twig',
            [
                'collection' => $collection,
                'entry' => $entry,
                'change' => $change
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws CollectionNotFoundException
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
        $collection = (int) $routerParams['collection'];
        $action = $routerParams['action'] ?? '';
        $entry = (int) ($routerParams['entry'] ?? 0);
        switch ($action) {
            case 'logs':
                if (!$entry) {
                    return $this->redirect('/admin/collection/' . $collection . '/entries');
                }
                $extra = $routerParams['extra'] ?? null;
                if ($extra) {
                    return $this->viewChangelog($request, $collection, $entry, (int) $extra);
                } else {
                    return $this->listRecentChanges($collection, $entry);
                }
            case 'edit':
                if (!$entry) {
                    return $this->redirect('/admin/collection/' . $collection . '/entries');
                }
                return $this->editEntry($request, $collection, $entry);
            case '':
                return $this->listEntries($collection);
            default:
                $this->messageOnce('Unknown action', 'error');
                return $this->redirect('/admin/collection/' . $collection . '/entries');
        }
    }
}
