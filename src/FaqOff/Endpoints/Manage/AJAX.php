<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use League\CommonMark\CommonMarkConverter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\StatusCode;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\ContributorAjaxFilter;
use Soatok\FaqOff\Splices\Accounts;
use Soatok\FaqOff\Splices\Authors;
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
    protected function addContributor(RequestInterface $request): ResponseInterface
    {
        $filter = new ContributorAjaxFilter();
        $postData = $this->post($request, self::TYPE_FORM, $filter);
        if (empty($postData)) {
            return $this->json(
                ['added' => false, 'error' => 'CSRF attack detected'],
                StatusCode::HTTP_FORBIDDEN
            );
        }
        $authorId = $postData['author'];
        $publicId = $postData['id'];

        /** @var Authors $authors */
        $authors = $this->splice('Authors');
        if (!$authors->accountIsOwner($postData['author'], $_SESSION['account_id'])) {
            return $this->json(
                ['added' => false, 'error' => 'You are not the owner!'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        /** @var Accounts $accounts */
        $accounts = $this->splice('Accounts');
        $accountId = $accounts->getAccountIdByPublicId($publicId);
        if (!$accountId) {
            return $this->json(
                ['added' => false, 'error' => 'There is no account with this ID.'],
                StatusCode::HTTP_NOT_FOUND
            );
        }
        if ($accountId === $_SESSION['account_id']) {
            return $this->json(
                ['added' => false, 'error' => 'That\'s your public ID, not theirs.'],
                StatusCode::HTTP_NOT_MODIFIED
            );
        }
        if ($authors->accountHasAccess($authorId, $accountId)) {
            return $this->json(
                ['added' => false, 'error' => 'Already a contributor'],
                StatusCode::HTTP_NOT_MODIFIED
            );
        }

        if ($authors->grantAccess($authorId, $accountId)) {
            return $this->json(['added' => true]);
        }
        return $this->json(['error' => 'Unknown status.']);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    protected function removeContributor(RequestInterface $request): ResponseInterface
    {
        $filter = new ContributorAjaxFilter();
        $postData = $this->post($request, self::TYPE_FORM, $filter);
        if (empty($postData)) {
            return $this->json(
                ['removed' => false, 'error' => 'CSRF attack detected'],
                StatusCode::HTTP_FORBIDDEN
            );
        }
        $authorId = $postData['author'];
        $publicId = $postData['id'];

        /** @var Authors $authors */
        $authors = $this->splice('Authors');
        if (!$authors->accountIsOwner($postData['author'], $_SESSION['account_id'])) {
            return $this->json(
                ['error' => 'You are not the owner!'],
                StatusCode::HTTP_FORBIDDEN
            );
        }

        /** @var Accounts $accounts */
        $accounts = $this->splice('Accounts');
        $accountId = $accounts->getAccountIdByPublicId($publicId);
        if (!$accountId) {
            return $this->json(
                ['removed' => false, 'error' => 'There is no account with this ID.'],
                StatusCode::HTTP_NOT_FOUND
            );
        }
        if ($accountId === $_SESSION['account_id']) {
            return $this->json(
                ['removed' => false, 'error' => 'That\'s your public ID, not theirs.'],
                StatusCode::HTTP_NOT_MODIFIED
            );
        }
        if (!$authors->accountHasAccess($authorId, $accountId)) {
            return $this->json(
                ['removed' => false, 'error' => 'Already not a contributor'],
                StatusCode::HTTP_NOT_MODIFIED
            );
        }

        if ($authors->revokeAccess($authorId, $accountId)) {
            return $this->json(['removed' => true]);
        }
        return $this->json(['error' => 'Unknown status.']);
    }

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
            case 'add-contributor':
                return $this->addContributor($request);
            case 'remove-contributor':
                return $this->removeContributor($request);
            case 'entry-search':
                return $this->entrySearch($request);
            case 'preview':
                return $this->preview($request);
            default:
                return $this->json(['error' => 'No action provided'], StatusCode::HTTP_NOT_ACCEPTABLE);
        }
    }
}
