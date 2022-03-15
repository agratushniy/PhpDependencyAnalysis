<?php

use Fhaculty\Graph\Graph;
use PhpParser\ParserFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(Finder::class);
    $services->set(Graph::class);
    $services->set(ParameterBag::class);
    $services->alias(ParameterBagInterface::class, ParameterBag::class);

    $services
        ->load('PhpParser\\', 'vendor/nikic/php-parser/lib/PhpParser/*')
        ->set(\PhpParser\Parser::class)
        ->factory([service(ParserFactory::class), 'create'])
        ->args([ParserFactory::PREFER_PHP7])
    ;

    $services->set(Parser::class);
};
