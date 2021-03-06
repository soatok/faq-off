<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Middleware;

use Psr\Http\Message\{
    MessageInterface,
    RequestInterface,
    ResponseInterface
};
use Slim\Http\{
    Headers,
    Response,
    StatusCode
};
use Soatok\AnthroKit\Auth\Fursona;
use Soatok\AnthroKit\Middleware;
use Soatok\FaqOff\AdminTrait;
use Soatok\FaqOff\MessageOnceTrait;
use Soatok\FaqOff\Utility;

/**
 * Class AdminsOnly
 * @package Soatok\FaqOff\Middleware
 */
class AdminsOnly extends Middleware
{
    use MessageOnceTrait;

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return MessageInterface
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): MessageInterface {
        $config = Fursona::autoConfig(
            $this->container->get(Fursona::CONTAINER_KEY) ?? []
        );
        $key = $config['session']['account_key'] ?? 'account_id';
        if (empty($_SESSION[$key])) {
            $k2 = $config['session']['auth_redirect_key'] ?? 'auth_redirect';
            $_SESSION[$k2] = $request->getUri()->getPath();
            return new Response(
                StatusCode::HTTP_FOUND,
                new Headers([
                    'Location' => $config['redirect']['login']
                ])
            );
        }
        // Get the admins
        $adminAccounts = Utility::getAdminAccountIDs();
        if (!in_array($_SESSION[$key], $adminAccounts, true)) {
            $this->messageOnce('This feature is only enabled for administrators.', 'error');
            return new Response(
                StatusCode::HTTP_FOUND,
                new Headers([
                    'Location' => '/manage'
                ])
            );
        }

        return $next($request, $response);
    }
}
