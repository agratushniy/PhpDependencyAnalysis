<?php

use Fhaculty\Graph\Graph;
use PhpDA\Command\Analyze;
use PhpDA\Mutator\GraphMutatorInterface;
use PhpDA\Parser\Visitor\Required\DeclaredNamespaceCollector;
use PhpDA\Parser\Visitor\Required\MetaNamespaceCollector;
use PhpDA\Parser\Visitor\Required\UsedNamespaceCollector;
use PhpDA\Strategy\StrategyInterface;
use PhpDA\Strategy\Usage;
use PhpParser\Parser\Php7;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\EnvPlaceholderParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $configurator
        ->parameters()
            ->set('options', [
                'source' => './src',
                'filePattern' => '*.php',
                'target' => 'phpda.svg',
                'groupLength' => 0,
            ])
    ;

    $services->load('PhpDA\\', 'src/*');
    $services->load('PhpParser\\', 'vendor/nikic/php-parser/lib/PhpParser/*');

    $services
        ->instanceof(Command::class)
        ->tag('console_command')
    ;

    # Vendor deps
    $services->set(Finder::class);
    $services->set(Graph::class);
    $services
        ->set(\PhpParser\Parser::class)
        ->factory([service(ParserFactory::class), 'create'])
        ->args([ParserFactory::PREFER_PHP7])
    ;
    $services->set(Parser::class);
    # Vendor deps

    $services
        #->instanceof(StrategyInterface::class)
        ->set(Usage::class)
        ->args([
            '$visitors' => [
                service(DeclaredNamespaceCollector::class),
                service(MetaNamespaceCollector::class),
                service(UsedNamespaceCollector::class)
            ],
            '$options' => '%options%',
            '$graphMutators' => tagged_iterator('graph_mutator')
        ])
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

    $services
        ->instanceof(GraphMutatorInterface::class)
        ->tag('graph_mutator');
};
