<?php

declare(strict_types=1);

namespace PhpDA\Tools\Container;

use PhpDA\Tools\FilesystemTools;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class LoadParametersPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $filesystemTools = new FilesystemTools();
        $parametersFile = $filesystemTools->getProjectDir() . '/config/parameters.php';
        $parametersLocalFile = $filesystemTools->getProjectDir() . '/parameters.local.php';

        if (is_readable($parametersLocalFile)) {
            $params = include_once $parametersLocalFile;
        } else {
            $params = include_once $parametersFile;
        }

        foreach ($params as $key => $value) {
            $container->setParameter($key, $value);
        }
    }
}
