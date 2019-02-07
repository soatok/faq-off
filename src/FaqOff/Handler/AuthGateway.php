<?php
namespace Soatok\FaqOff\Handler;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\Exception\ContainerException;
use Soatok\FaqOff\Entity\User;
use Soatok\FaqOff\Filter\LoginFilter;
use Soatok\FaqOff\Filter\RegisterFilter;
use Soatok\FaqOff\HandlerContainerTrait;
use Soatok\FaqOff\HandlerInterface;
use Soatok\FaqOff\Utility;

/**
 * Class AuthGateway
 * @package Soatok\FaqOff\Handler
 */
class AuthGateway implements HandlerInterface
{
    use HandlerContainerTrait;

    /** @var User $user */
    private $user;

    /**
     * @throws ContainerException
     */
    public function init()
    {
        $this->user = new User(
            $this->getDatabase(),
            $this->container->get('settings')['encryption_key']
        );
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws \SodiumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function __invoke(RequestInterface $request): ResponseInterface
    {
        switch ($request->getUri()->getPath()) {
            case '/auth/login':
                return $this->login($request);
            case '/auth/register':
                return $this->register($request);
        }
        return $this->errorPage(404);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function login(RequestInterface $request): ResponseInterface
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->container->get('twig');

        $post = Utility::getPostVars($request, new LoginFilter());
        if ($post) {
            $user_id = $this->user->login($post);
            if ($user_id) {
                $_SESSION['user_id'] = $user_id;
                // Registration successful
                \header('Location: /');
                exit;
                // TODO: redirect
            } else {
                $twig->addGlobal('auth_error', true);
            }
        }

        $body = $twig->render('login.twig');
        return Utility::createResponse($body);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     *
     * @throws ContainerException
     * @throws \SodiumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function register(RequestInterface $request): ResponseInterface
    {
        /** @var \Twig_Environment $twig */
        $twig = $this->container->get('twig');

        $post = Utility::getPostVars($request, new RegisterFilter());
        if ($post) {
            if ($this->user->register($post)) {
                // Registration successful
                \header('Location: /auth/login');
                exit;
                // TODO: redirect
            } else {
                $twig->addGlobal('reg_error', true);
            }
        }

        $body = $twig->render('register.twig');
        return Utility::createResponse($body);
    }
}
