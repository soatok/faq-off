<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\IntArrayFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditAuthorFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditAuthorFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('screenname', new StringFilter())
            ->addFilter('biography', new StringFilter())
            ->addFilter('contributors', new IntArrayFilter())
        ;
    }
}
