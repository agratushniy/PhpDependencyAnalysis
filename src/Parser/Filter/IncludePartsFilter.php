<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

class IncludePartsFilter implements NodeNameFilterInterface
{
    /**
     * @var array|Name[]
     */
    protected array $namespaces;

    public function __construct(array $configuration)
    {
        $this->namespaces = [];

        foreach ($configuration as $namespace) {
            $this->namespaces[] = new Name($namespace);
        }
    }

    public function filter(Name $name): bool
    {
        if (empty($this->namespaces)) {
            return true;
        }

        foreach ($this->namespaces as $namespace) {
            $rootSeparator = '';

            if ($name->isFullyQualified()) {
                $rootSeparator = '\\';
            }

            if (str_starts_with($name->toCodeString(), $rootSeparator . $namespace->toCodeString())) {
                return true;
            }
        }

        return false;
    }
};
