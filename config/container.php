<?php

use PhpDA\Layout\Aggregation;
use PhpDA\Layout\GraphViz;
use PhpDA\Layout\GroupLayoutBuilder;
use PhpDA\Layout\LayoutProviderInterface;
use PhpDA\Mutator\GroupByCustomConfiguration;
use PhpDA\Parser\Filter\ChainOfFilters;
use PhpDA\Parser\Filter\EmptyNodeNameFilter;
use PhpDA\Parser\Filter\IgnoredNamespacesFilter;
use PhpDA\Parser\Filter\IncludePartsFilter;
use PhpDA\Parser\Filter\NodeNameFilterInterface;
use PhpDA\Parser\Filter\PhpInternalFunctionsFilter;
use PhpDA\Parser\Filter\TaggedFilter;
use PhpDA\Parser\NameTransformer\SliceTransformer;
use PhpDA\Parser\Visitor\Required\DeclaredNamespaceCollector;
use PhpDA\Parser\Visitor\Required\UsedNamespaceCollector;
use PhpDA\Strategy\Strategy;
use PhpDA\Writer\Strategy\Svg;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

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
                'target' => '/app/phpda.svg'
            ])
            ->set('groups', [
                [
                    'title' => 'Mamba common',
                    'tag' => 'business',
                    'items' => [
                        'Mamba\Context',
                        'Mamba\CommandBus'
                    ]
                ],
                [
                    'tag' => 'business',
                    'color' => '#FFFFFFF'
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
                        'Anketa'
                    ]
                ],
                [
                    'title' => 'Symfony',
                    'items' => [
                        'Symfony'
                    ]
                ],
                [
                    'title' => 'Mamba infrastructure',
                    'items' => [
                        'RabbitMQ'
                    ]
                ]
            ])
            ->set('filter.include', [
                /*'Mamba\Comments',
                'Hitlist',
                'Anketa',
                'Symfony'*/
            ])
            ->set('filter.tags.root_nodes', [
                'Mamba\Comments',
            ])
            ->set('filter.tags.supported', [
                'framework', 'infrastructure'
            ])
            ->set('filter.tags.collection', [
                'business' => [
                    'Mamba\Comments',
                    'Hitlist',
                    'Anketa'
                ],
                'framework' => [
                    'Symfony'
                ],
                'infrastructure' => [
                    'RabbitMQ'
                ]
            ])
    ;

    $services->load('PhpDA\\', 'src/*');

    $services
        ->set(Application::class)
        ->public()
    ;

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
        ->set(GraphViz::class)
        ->call('setGroupLayoutBuilder', [service(GroupLayoutBuilder::class)]);


    $services
        ->set(GroupByCustomConfiguration::class)
        ->args([
            '$groupsConfiguration' => '%groups%'
        ])
        ->tag('graph_mutator')
    ;

    $services
        ->set(SliceTransformer::class)
        ->arg('$options', ['offset' => 0, 'length' => 2]);



    $services->alias(NodeNameFilterInterface::class, ChainOfFilters::class);
    $services
        ->set(ChainOfFilters::class)
        ->arg('$filters', [
            service(EmptyNodeNameFilter::class),
            service(IgnoredNamespacesFilter::class),
            service(PhpInternalFunctionsFilter::class),
            #service(IncludePartsFilter::class),
            service(TaggedFilter::class)
        ]);
    $services
        ->set(IncludePartsFilter::class)
        ->arg('$configuration', '%filter.include%');
    $services
        ->set(TaggedFilter::class)
        ->arg('$supportedTags', '%filter.tags.supported%')
        ->arg('$taggedCollection', '%filter.tags.collection%')
        ->arg('$rootNodes', '%filter.tags.root_nodes%')
    ;
};
