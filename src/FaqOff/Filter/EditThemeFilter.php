<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Filter;

use ParagonIE\Ionizer\Filter\StringArrayFilter;
use ParagonIE\Ionizer\Filter\StringFilter;
use ParagonIE\Ionizer\InputFilterContainer;
use Soatok\FaqOff\Utility;

/**
 * Class EditThemeFilter
 * @package Soatok\FaqOff\Filter
 */
class EditThemeFilter extends InputFilterContainer
{
    public function __construct()
    {
        $this
            ->addFilter('name', new StringFilter())
            ->addFilter('description', new StringFilter())
            ->addFilter('js_files', new StringArrayFilter())
            ->addFilter('css_files', new StringArrayFilter())
            ->addFilter(
                'twig_vars',
                (new StringFilter())->addCallback(function (string $in): string {
                    return Utility::validateJson($in);
                })
            );
    }
}
