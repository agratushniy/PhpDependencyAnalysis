<?php

declare(strict_types=1);

namespace PhpDA\Container;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Reference;

final class RegisterConsoleCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $consoleApplication = $container->findDefinition(Application::class);

        foreach ($container->findTaggedServiceIds('console_command') as $commandId => $tags) {
            $consoleApplication->addMethodCall('add', [new Reference($commandId)]);
        }
    }
}
