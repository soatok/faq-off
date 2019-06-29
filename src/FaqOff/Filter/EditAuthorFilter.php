<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class EditAuthorFilter
 * @package Soatok\FaqOff\Filter
 */
class EditAuthorFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('biography', new StringFilter());
    }
}
