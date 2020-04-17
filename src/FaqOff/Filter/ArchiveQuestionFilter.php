<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\IntFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class ArchiveQuestionFilter
 * @package Soatok\FaqOff\Filter
 */
class ArchiveQuestionFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this->addFilter('question_id', new IntFilter());
    }
}
