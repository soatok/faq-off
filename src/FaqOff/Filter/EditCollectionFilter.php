<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\IntFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class EditCollectionFilter
 * @package Soatok\FaqOff\Filter
 */
class EditCollectionFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('description', new StringFilter())
            ->addFilter('theme', new IntFilter())
            ->addFilter('title', new StringFilter());
    }
}
