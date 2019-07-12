<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminMkdirFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminMkdirFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this->addFilter('parent', new StringFilter())
            ->addFilter('dirname', new StringFilter());
    }
}
