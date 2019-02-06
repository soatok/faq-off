<?php
declare(strict_types=1);
namespace Soatok\FaqOff;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HandlerInterface
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request):  ResponseInterface;
}
