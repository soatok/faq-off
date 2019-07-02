<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class CreateAuthorFilter
 * @package Soatok\FaqOff\Filter
 */
class CreateAuthorFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('name', new StringFilter())
            ->addFilter('biography', new StringFilter());
    }
}
