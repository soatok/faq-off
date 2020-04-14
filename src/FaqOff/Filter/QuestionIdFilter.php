<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\IntFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class QuestionIdFilter
 * @package Soatok\FaqOff\Filter
 */
class QuestionIdFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('question', new IntFilter());
    }
}
