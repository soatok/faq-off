<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminListDirFilter
 */
class AdminListDirFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this->addFilter('directory', new StringFilter());
    }
}
