<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

final class ChainOfFilters implements NodeNameFilterInterface
{
    public function __construct(private iterable $filters)
    {
    }

    public function filter(Name $name): bool
    {
        foreach ($this->filters as $filter) {
            /**
             * @var NodeNameFilterInterface $filter
             */
            if (!$filter->filter($name)) {
                return false;
            }
        }

        return true;
    }
}
