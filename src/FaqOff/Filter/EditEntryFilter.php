<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\IntArrayFilter;
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
            ->addFilter('question_box', new BoolFilter())
            ->addFilter('title', new StringFilter())
            ->addFilter('contents', new StringFilter())
            ->addFilter('follow-up', new IntArrayFilter())
            ->addFilter('index-me', new BoolFilter())
            ->addFilter('opengraph_image_url', new StringFilter());
    }
}
