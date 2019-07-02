<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;

/**
 * Class CreateCollectionFilter
 * @package Soatok\FaqOff\Filter
 */
class CreateCollectionFilter extends InputFilterContainer
{
    public function __construct(array $config = [])
    {
        $this
            ->addFilter('title', new StringFilter())
            ->addFilter('url', new StringFilter());
    }
}
