<?php
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

class AdminEditEntryFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('title', new StringFilter())
            ->addFilter('contents', new StringFilter())
            ->addFilter('options', new StringFilter()) // JSON
        ;
    }
}