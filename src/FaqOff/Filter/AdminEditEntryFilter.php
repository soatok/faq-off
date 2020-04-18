<?php
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

class AdminEditEntryFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('question_box', new BoolFilter())
            ->addFilter('title', new StringFilter())
            ->addFilter('contents', new StringFilter())
            ->addFilter('options', new StringFilter()) // JSON
            ->addFilter('opengraph_image_url', new StringFilter())
        ;
    }
}
