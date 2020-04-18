<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\IntArrayFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class CreateEntryFilter
 * @package Soatok\FaqOff\Filter
 */
class CreateEntryFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('question_box', new BoolFilter())
            ->addFilter('title', new StringFilter())
            ->addFilter('contents', new StringFilter())
            ->addFilter('attach-to', new IntArrayFilter())
            ->addFilter('index-me', new BoolFilter())
            ->addFilter('opengraph_image_url', new StringFilter());
    }
}
