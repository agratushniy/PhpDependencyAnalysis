<?php

declare(strict_types=1);

namespace PhpDA\Tools\Container;

use PhpDA\Mutator\GroupByCustomConfiguration;
use PhpDA\Parser\Filter\TaggedFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TaggedGroupsParserPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('tagger_groups');

        if (empty($config)) {
            return;
        }

        $groupsDefinition = $container->getDefinition(GroupByCustomConfiguration::class);
        $groupsConfig = [];

        foreach ($config['items'] as $item) {
            $groupsConfig[] = [
                'title' => $item['title'],
                'items' => $item['items']
            ];
        }

        $groupsDefinition->setArgument(0, $groupsConfig);

        $tagsConfig = [];
        $taggedFilterDefinition = $container->getDefinition(TaggedFilter::class);

        foreach ($config['items'] as $item) {
            if (!isset($item['tag'])) {
                continue;
            }

            if (!isset($tagsConfig[$item['tag']])) {
                $tagsConfig[$item['tag']] = [];
            }

            $tagsConfig[$item['tag']] = array_merge($tagsConfig[$item['tag']], $item['items']);
        }

        $taggedFilterDefinition
            ->setArgument(0, $config['filter']['use_tags'])
            ->setArgument(1, $tagsConfig)
            ->setArgument(2, $config['filter']['root_names'])
        ;
    }
}
