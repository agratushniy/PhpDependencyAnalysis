<?php

declare(strict_types=1);

namespace PhpDA;

use Symfony\Component\Console\Output\OutputInterface;

interface HasOutputInterface
{
    public function setOutput(OutputInterface $output): void;
}
