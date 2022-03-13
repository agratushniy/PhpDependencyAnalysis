<?php

declare(strict_types=1);

namespace PhpDA\Entity;

use PhpParser\Node\Name;

class Stmt extends \PhpParser\Node\Stmt
{
    public function __construct(private \PhpParser\Node\Stmt $decorated)
    {
        parent::__construct($this->decorated->getAttributes());
    }

    public function originalStmt(): \PhpParser\Node\Stmt
    {
        return $this->decorated;
    }

    public function getType(): string
    {
        return $this->decorated->getType();
    }

    public function getSubNodeNames(): array
    {
        return $this->decorated->getSubNodeNames();
    }

    public function getNamespace(): ?Name
    {
        $props = get_object_vars($this->decorated);

        return $props['namespacedName'] ?? null;
    }
}
