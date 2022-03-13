<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

final class PhpInternalFunctionsFilter implements NodeNameFilterInterface
{
    private mixed $functions;

    public function __construct()
    {
        $this->functions = get_defined_functions()['internal'];
    }

    public function filter(Name $name): bool
    {
        return !in_array($name->toString(), $this->functions);
    }
}
