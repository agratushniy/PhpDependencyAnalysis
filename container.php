<?php

use PhpDA\Command\Analyze;
use PhpDA\Strategy\StrategyInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load('PhpDA\\', 'src/*');
    $services->load('PhpParser\\', 'vendor/nikic/php-parser/lib/PhpParser/*');

    $services
        ->instanceof(Command::class)
        ->tag('console_command')
    ;

    # Vendor deps
    $services->set(\Symfony\Component\Finder\Finder::class);
    $services->set(\Fhaculty\Graph\Graph::class);
    $services->alias(\PhpParser\Parser::class, \PhpParser\Parser\Php7::class);
    # Vendor deps

    $services
        #->instanceof(StrategyInterface::class)
        ->set(\PhpDA\Strategy\Usage::class)
        ->tag('strategy', ['key' => 'usage']);

    $services
        ->set(Application::class)
        ->public()
    ;

    $services
        ->set(Analyze::class)
        ->args([
            tagged_iterator('strategy', 'key')
        ]);
};
