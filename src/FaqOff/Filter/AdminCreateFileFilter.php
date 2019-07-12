<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminCreateFileFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminCreateFileFilter extends InputFilterContainer
{
    /**
     * AdminCreateFileFilter constructor.
     */
    public function __construct()
    {
        $this->addFilter('path', new StringFilter())
            ->addFilter('file', new StringFilter())
            ->addFilter('contents', new StringFilter());
    }
}
