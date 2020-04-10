<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\BoolFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class FrontQuestionFilter
 * @package Soatok\FaqOff\Filter
 */
class FrontQuestionFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('question', new StringFilter())
            ->addFilter('attribution', new BoolFilter());
    }
}
