<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services
        ->instanceof(Command::class)
        ->tag('console_command')
        ->load('PhpDA\\', 'src/*')
    ;

    $services
        ->set(Application::class)
        ->public()
    ;
};
