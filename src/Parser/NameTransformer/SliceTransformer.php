<?php

declare(strict_types=1);

namespace PhpDA\Parser\NameTransformer;

use PhpParser\Node\Name;

final class SliceTransformer implements NodeNameTransformerInterface
{
    public function __construct(private array $options)
    {
    }

    public function transform(Name $name): Name
    {
        if (!isset($this->options['offset']) && !isset($this->options['length'])) {
            return $name;
        }

        if (!isset($this->options['length'])) {
            $name->parts = array_slice($name->parts, (int)$this->options['offset']);

            return $name;
        }

        $name->parts = array_slice($name->parts, (int)$this->options['offset'], (int)$this->options['length']);

        return $name;
    }
}
