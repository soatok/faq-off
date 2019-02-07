<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class RegisterFilter
 * @package Soatok\FaqOff\Filter
 */
class RegisterFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('username', new StringFilter())
            ->addFilter('passphrase', new StringFilter());
    }
}
