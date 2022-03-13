<?php

declare(strict_types=1);

namespace PhpDA\Layout;

interface GroupLayoutBuilder
{
    public function support(int $groupId): bool;

    public function buildLayout(int $groupId): string;
}
