<?php

declare(strict_types=1);

namespace PhpDA\Parser\Filter;

use PhpParser\Node\Name;

final class TaggedFilter extends IncludePartsFilter
{
    public function __construct(private array $supportedTags, private array $taggedCollection, private array $rootNodes)
    {
        parent::__construct([]);
    }

    private function isRootNode(Name $name): bool
    {
        if (empty($this->rootNodes)) {
            return false;
        }

        $this->namespaces = [];

        foreach ($this->rootNodes as $node) {
            $this->namespaces[] = new Name($node);
        }

        return parent::filter($name);
    }

    public function filter(Name $name): bool
    {
        if (empty($this->supportedTags)) {
            return true;
        }

        if ($this->isRootNode($name)) {
            return true;
        }

        foreach ($this->supportedTags as $validTag) {
            if (!isset($this->taggedCollection[$validTag])) {
                continue;
            }

            $this->namespaces = [];

            foreach ($this->taggedCollection[$validTag] as $namespace) {
                $this->namespaces[] = new Name($namespace);
            }

            if (parent::filter($name)) {
                return true;
            }
        }

        return false;
    }
}
