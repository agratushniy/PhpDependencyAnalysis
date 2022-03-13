<?php

declare(strict_types=1);

namespace PhpDA\Entity;

use PhpParser\Node\Name;

final class Group
{
    public function __construct(private int $id, private string $title, private array $items)
    {
    }

    public static function undefined(int $id): self
    {
        return new self($id, 'Undefined', []);
    }

    public function contains(Name $name): bool
    {
        foreach ($this->items as $namespace) {
            if (str_starts_with($name->toCodeString(), $namespace)) {
                return true;
            }
        }

        return false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
