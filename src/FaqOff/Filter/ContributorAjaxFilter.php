<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;


use ParagonIE\Ionizer\Filter\IntFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

class ContributorAjaxFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('id', new StringFilter())
            ->addFilter('author', new IntFilter());
    }
}
