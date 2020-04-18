<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\IntFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditCollectionFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditCollectionFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('question_box', new BoolFilter())
            ->addFilter('author', new IntFilter())
            ->addFilter('description', new StringFilter())
            ->addFilter('opengraph_image_url', new StringFilter())
            ->addFilter('theme', new IntFilter())
            ->addFilter('title', new StringFilter())
            ->addFilter('url', new StringFilter());
    }
}
