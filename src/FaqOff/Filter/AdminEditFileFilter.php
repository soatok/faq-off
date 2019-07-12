<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditFileFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditFileFilter extends InputFilterContainer
{
    /**
     * AdminEditFileFilter constructor.
     */
    public function __construct()
    {
        $this->addFilter('path', new StringFilter())
            ->addFilter('file', new StringFilter())
            ->addFilter('contents', new StringFilter());
    }
}
