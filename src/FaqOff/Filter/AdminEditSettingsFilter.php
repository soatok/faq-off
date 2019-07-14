<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditSettingsFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditSettingsFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this->addFilter('contents', new StringFilter());
    }
}
