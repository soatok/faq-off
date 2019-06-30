<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Endpoints;

use Interop\Container\Exception\ContainerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Soatok\AnthroKit\Endpoint;
use Soatok\FaqOff\MessageOnceTrait;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
        $error = $routerParams['error'] ?? '';
        switch ($error) {
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
        }
        return $this->redirect('/');
    }
}
