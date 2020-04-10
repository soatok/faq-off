<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Soatok\AnthroKit\Splice;
use Soatok\FaqOff\Splices\Questions;

/**
 * Trait FrontAccountInfoTrait
 * @package Soatok\FaqOff
 * @property Container $container
 * @method Splice splice(string $name, ?string $namespace = null)
 * @method Response redirect(string $url, int $status = StatusCode::HTTP_FOUND, bool $safe = false)
 */
trait FrontAccountInfoTrait
{
    /** @var Questions $questions */
    protected $questions;

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['account_id']);
    }

    /**
     * @param array $post
     * @param string $type
     * @param array $object
     * @param string $redirect
     * @return ResponseInterface
     */
    public function addQuestion(
        array $post,
        string $type,
        array $object,
        string $redirect
    ): ResponseInterface {
        if ($type === 'entry') {
            $this->questions->createForEntry(
                (int) $object['entryid'],
                $post['question'],
                $_SESSION['account_id'],
                $post['attribution']
            );
        } elseif ($type === 'collection') {
            $this->questions->createForCollection(
                (int) $object['collectionid'],
                $post['question'],
                $_SESSION['account_id'],
                $post['attribution']
            );
        } else {
            return $this->redirect('/@' . $object['author_screenname']);
        }
        return $this->redirect($redirect);
    }
}
