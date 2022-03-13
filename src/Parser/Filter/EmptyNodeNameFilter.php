<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

final class EmptyNodeNameFilter implements NodeNameFilterInterface
{
    public function filter(Name $name): bool
    {
        return count($name->parts) > 0;
    }
}
