<?php

use Fhaculty\Graph\Graph;
use PhpDA\Layout\Aggregation;
use PhpDA\Layout\GraphViz;
use PhpDA\Layout\GroupLayoutBuilder;
use PhpDA\Layout\LayoutProviderInterface;
use PhpDA\Mutator\GroupByCustomConfiguration;
use PhpDA\Parser\Filter\IncludePartsFilter;
use PhpDA\Parser\NameTransformer\SliceTransformer;
use PhpDA\Parser\Visitor\Required\DeclaredNamespaceCollector;
use PhpDA\Parser\Visitor\Required\UsedNamespaceCollector;
use PhpDA\Strategy\Strategy;
use PhpDA\Writer\Strategy\Svg;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services
        ->instanceof(Command::class)
        ->tag('console_command')
    ;

    $configurator
        ->parameters()
            ->set('options', [
                'source' => '/mamba/modules/uni-comments',
                'filePattern' => '*.php',
                'target' => 'phpda.svg'
            ])
    ;

    $services->load('PhpDA\\', 'src/*');
    $services->load('PhpParser\\', 'vendor/nikic/php-parser/lib/PhpParser/*');

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

    $services->alias(LayoutProviderInterface::class, Aggregation::class);
    $services
        ->set(Aggregation::class)
        ->args(['Mamba graph'])
    ;

    $services
        ->set(Strategy::class)
        ->args([
            '$visitors' => [
                service(DeclaredNamespaceCollector::class),
                #service(MetaNamespaceCollector::class),
                service(UsedNamespaceCollector::class)
            ],
            '$options' => '%options%',
            '$graphMutators' => tagged_iterator('graph_mutator'),
            '$graphWriter' => service(Svg::class)
        ])
        ->tag('strategy')
    ;

    $services
        ->set(Svg::class)
        ->arg('$targetFilePath', '/app/phpda.svg');

    $services
        ->set(Application::class)
        ->public()
    ;

    $services
        ->set(GraphViz::class)
        ->call('setGroupLayoutBuilder', [service(GroupLayoutBuilder::class)]);


    $services
        ->set(IncludePartsFilter::class)
        ->arg('$configuration', [
            'Mamba\Comments',
            'Hitlist',
            'Anketa'
        ]);

    $services
        ->set(GroupByCustomConfiguration::class)
        ->args([
            '$groupsConfiguration' => [
                [
                    'title' => 'Group 1',
                    'items' => [
                        'Mamba\Context',
                        'Mamba\CommandBus'
                    ]
                ],
                [
                    'title' => 'Comments',
                    'items' => [
                        'Mamba\Comments'
                    ]
                ],
                [
                    'title' => 'Hitlist',
                    'items' => [
                        'Hitlist'
                    ]
                ],
                [
                    'title' => 'Anketa',
                    'items' => [
                        'Anketa\\'
                    ]
                ]
            ]
        ])
        ->tag('graph_mutator')
    ;

    $services
        ->set(SliceTransformer::class)
        ->arg('$options', ['offset' => 0, 'length' => 2]);
};
