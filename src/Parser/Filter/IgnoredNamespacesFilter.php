<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

final class IgnoredNamespacesFilter implements NodeNameFilterInterface
{
    public function filter(Name $name): bool
    {
        return !in_array(strtolower($name->toString()), ['self', 'parent', 'static', 'null', 'true', 'false']);
    }
}
