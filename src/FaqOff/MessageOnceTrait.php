<?php
declare(strict_types=1);
namespace Soatok\FaqOff;

/**
 * Trait MessasgeOnce
 * @package Soatok\FaqOff
 */
trait MessageOnceTrait
{
    /**
     * @param string $message
     * @param string|null $class
     * @return void
     */
    public function messageOnce(string $message = '', ?string $class = null): void
    {
        if ($class) {
            $message = '<span class="' . $class . '">' . $message . '</span>';
        }
        $_SESSION['message_once'] []= $message;
    }
}
