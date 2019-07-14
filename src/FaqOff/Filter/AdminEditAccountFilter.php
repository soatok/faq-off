<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditAccountFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditAccountFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('active', new BoolFilter())
            ->addFilter('disable-external', new BoolFilter())
            ->addFilter('disable-two-factor', new BoolFilter())
            ->addFilter('email', new StringFilter())
            ->addFilter('login', new StringFilter())
            ->addFilter('password', new StringFilter())
            ->addFilter('password2', new StringFilter())
            ->addFilter('public_id', new StringFilter())
        ;
    }
}
