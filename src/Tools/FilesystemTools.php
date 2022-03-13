<?php

declare(strict_types=1);

namespace PhpDA\Tools;

use LogicException;
use ReflectionObject;

use function dirname;

final class FilesystemTools
{
    private string $projectDir;

    public function getProjectDir(): string
    {
        if (!isset($this->projectDir)) {
            $r = new ReflectionObject($this);

            if (!is_file($dir = $r->getFileName())) {
                throw new LogicException('Cannot auto-detect project dir');
            }

            $dir = $rootDir = dirname($dir);
            while (!is_file($dir.'/composer.json')) {
                if ($dir === dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = dirname($dir);
            }
            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }
}
