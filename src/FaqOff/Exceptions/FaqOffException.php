<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Exceptions;

use ParagonIE\Corner\{
    CornerInterface,
    CornerTrait
};

/**
 * Class FaqOffException
 * @package Soatok\FaqOff\Exceptions
 */
class FaqOffException extends \Exception implements CornerInterface
{
    use CornerTrait;
}
