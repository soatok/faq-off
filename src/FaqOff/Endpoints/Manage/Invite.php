<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints\Manage;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\{
    RequestInterface,
    ResponseInterface
};
use Slim\Container;
use Soatok\AnthroKit\Auth\Fursona;
use Twig\Error\{
    LoaderError,
    RuntimeError,
    SyntaxError
};
use Soatok\FaqOff\BackendEndpoint;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Splices\Accounts;

/**
 * Class Invite
 * @package Soatok\FaqOff\Endpoints\Manage
 */
class Invite extends BackendEndpoint
{
    use MessageOnceTrait;

    /** @var array<string, string|array> $config */
    protected $config;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $config = $container->get(Fursona::CONTAINER_KEY) ?? [];
        $this->config = Fursona::autoConfig($config);
        $this->accounts->setConfig($this->config);
    }

    /**
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function ajaxCreateCode(): ResponseInterface
    {
        $code = $this->accounts->createInviteCode($_SESSION['account_id']);
        return $this->json([
            'status' => 'SUCCESS',
            'code' => $code
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
    protected function viewInvitePage(RequestInterface $request): ResponseInterface
    {
        $post = $this->post($request);
        if (isset($post['create-code'])) {
            $this->accounts->createInviteCode($_SESSION['account_id']);
        }
        $unused = $this->accounts->getUnusedInviteCodes($_SESSION['account_id']);
        return $this->view('manage/invite.twig', [
            'baseurl' => $this->getBaseUrl(),
            'unused_codes' => $unused
        ]);
    }

    /**
     * @return string
     */
    protected function getBaseUrl(): string
    {
        $secure = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return htmlentities(
            $secure . '://' . $host . '/auth/invite'
        );
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
        if (!$this->accounts->accountCanInvite($_SESSION['account_id'])) {
            $this->messageOnce(
                'You do not have permission to invite new users.',
                'error'
            );
            return $this->redirect('/manage');
        }
        if (empty($routerParams)) {
            return $this->viewInvitePage($request);
        }
        if ($routerParams['action'] === 'create') {
            return $this->ajaxCreateCode();
        }
        return $this->redirect('/manage/invite');
    }
}
