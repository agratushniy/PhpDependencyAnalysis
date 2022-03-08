<?php

declare(strict_types=1);

namespace PhpDA\Mutator;

use Fhaculty\Graph\Graph;

interface GraphMutatorInterface
{
    public function mutate(Graph $graph): void;
}
