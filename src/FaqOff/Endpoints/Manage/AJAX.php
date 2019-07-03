<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Exceptions\CollectionNotFoundException;
use Soatok\FaqOff\Splices\Entry;
use Soatok\FaqOff\Splices\EntryCollection;

/**
 * Class AJAX
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class AJAX extends Endpoint
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function entrySearch(RequestInterface $request): ResponseInterface
    {
        $getData = $this->get($request);

        /*
        if (empty($getData['collection-id'])) {
            return $this->json(
                ['error' => 'Collection not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }
        */
        if (empty($getData['q'])) {
            return $this->json([]);
        }
        // $collectionId = (int) $getData['collection-id'];

        /** @var EntryCollection $collection */
        // $collection = $this->splice('EntryCollection');

        /** @var Entry $entries */
        $entries = $this->splice('Entry');

        /*
        try {
            $collection->getById($collectionId);
        } catch(CollectionNotFoundException $ex) {
            return $this->json(
                ['error' => 'Collection not found'],
                StatusCode::HTTP_NOT_FOUND
            );
        }
        */
        return $this->json([
            'results' => $entries->entrySearch($getData['q'])
        ]);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function preview(RequestInterface $request): ResponseInterface
    {
        $postData = $this->getPostBody($request);
        if (empty($postData['markdown'])) {
            return $this->json(
                ['error' => 'No "markdown" data provided'],
                StatusCode::HTTP_NOT_ACCEPTABLE
            );
        }
        /** @var CommonMarkConverter $converter */
        $converter = $this->container['markdown'];

        /** @var \HTMLPurifier $purifier */
        $purifier = $this->container['purifier'];

        $rendered = $converter->convertToHtml($postData['markdown']);
        return $this->json([
            'status' => 'SUCCESS',
            'preview' => $purifier->purify($rendered)
        ]);
    }

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
        $action = $routerParams['action'] ?? '';
        switch ($action) {
            case 'entry-search':
                return $this->entrySearch($request);
            case 'preview':
                return $this->preview($request);
            default:
                return $this->json(['error' => 'No action provided'], StatusCode::HTTP_NOT_ACCEPTABLE);
        }
    }
}
