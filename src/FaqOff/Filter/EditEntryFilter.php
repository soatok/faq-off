<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class EditEntryFilter
 * @package Soatok\FaqOff\Filter
 */
class EditEntryFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('title', new StringFilter());
    }
}
