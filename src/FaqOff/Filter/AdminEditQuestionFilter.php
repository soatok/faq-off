<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\{
    BoolFilter,
    IntFilter,
    StringFilter
};
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class AdminEditQuestionFilter
 * @package Soatok\FaqOff\Filter
 */
class AdminEditQuestionFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('archived', new BoolFilter())
            ->addFilter('attribution', new BoolFilter())
            ->addFilter('collection', new IntFilter())
            ->addFilter('entry', new IntFilter())
            ->addFilter('question', new StringFilter());
    }
}
