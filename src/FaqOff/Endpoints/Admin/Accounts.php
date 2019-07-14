<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Admin;

use Interop\Container\Exception\ContainerException;
use ParagonIE\Ionizer\InvalidDataException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\Filter\AdminEditAccountFilter;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Accounts as AccountSplice;
use Soatok\FaqOff\Splices\Authors;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class Accounts
 * @package Soatok\FaqOff\Endpoints\Admin
 */
class Accounts extends Endpoint
{
    use MessageOnceTrait;

    /** @var AccountSplice $accounts */
    protected $accounts;

    /** @var Authors $authors */
    protected $authors;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->accounts = $this->splice('Accounts');
        $this->authors = $this->splice('Authors');
    }

    /**
     * @param RequestInterface $request
     * @param int $accountId
     * @return ResponseInterface
     * @throws ContainerException
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function editAccount(RequestInterface $request, int $accountId): ResponseInterface
    {
        $filter = new AdminEditAccountFilter();
        $post = $this->post($request, self::TYPE_FORM, $filter);
        if ($post) {
            if ($this->accounts->updateAccountByAdmin($accountId, $post)) {
                $this->messageOnce('Account updated successfully.', 'success');
                return $this->redirect('/admin/account/edit/' . $accountId);
            }
        }
        return $this->view(
            'admin/accounts-edit.twig',
            [
                'account' => $this->accounts->getInfoByAccountId($accountId)
            ]
        );
    }

    /**
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function listAccounts(): ResponseInterface
    {
        return $this->view(
            'admin/accounts-list.twig',
            [
                'accounts' => $this->accounts->listAllWithPublicId()
            ]
        );
    }
    /**
     * @param RequestInterface $request
     * @param int $accountId
     * @return ResponseInterface
     * @throws ContainerException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function viewAccount(RequestInterface $request, int $accountId): ResponseInterface
    {
        return $this->view(
            'admin/accounts-view.twig',
            [
                'account' => $this->accounts->getInfoByAccountId($accountId),
                'authors' => $this->authors->getByAccount($accountId),
            ]
        );
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @param array $routerParams
     * @return ResponseInterface
     * @throws ContainerException
     * @throws InvalidDataException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        RequestInterface $request,
        ?ResponseInterface $response = null,
        array $routerParams = []
    ): ResponseInterface {
        $action = $routerParams['action'] ?? null;
        switch ($action) {
            case 'edit':
                return $this->editAccount($request, (int) $routerParams['id']);
            case 'view':
                return $this->viewAccount($request, (int) $routerParams['id']);
            case '':
                return $this->listAccounts();
            default:
                return $this->redirect('/admin/accounts');
        }
    }
}
