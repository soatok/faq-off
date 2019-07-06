<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\MessageOnceTrait;

/**
 * Class GenericError
 * @package Soatok\FaqOff\Endpoints
 */
class GenericError extends Endpoint
{
    use MessageOnceTrait;

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
        /** @var Request $request */
        $error = $routerParams['error'] ?? '';
        switch ($error) {
            case 'account-banned':
                $this->messageOnce('Authentication failed: Account is not active.', 'error');
                break;
            case 'auth-failure':
                $this->messageOnce('Authentication failed.', 'error');
                break;
            case 'empty-params':
                $this->messageOnce('Empty parameters provided.', 'error');
                break;
            case 'invalid-action':
                $this->messageOnce('Invalid action.', 'error');
                break;
            case 'invite-required':
                $this->messageOnce('Account registration requires an invitation.', 'error');
                break;
            case 'logout-fail':
                $this->messageOnce('Logout unsuccessful.', 'error');
                break;
            case 'twitter-error':
                $this->messageOnce('Twitter failed.', 'error');
                $error = $request->getQueryParam('error', '');
                if ($error) {
                    $this->messageOnce($error, 'error');
                }
                break;
        }
        return $this->redirect('/');
    }
}
