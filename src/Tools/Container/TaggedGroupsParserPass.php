<?php

declare(strict_types=1);

namespace PhpDA\Tools\Container;

use PhpDA\Mutator\GroupsByCustomConfiguration;
use PhpDA\Parser\Filter\TaggedFilter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TaggedGroupsParserPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('tagger_groups');
        $tagsColorConfig = $container->getParameter('tags_colors');

        if (empty($config)) {
            return;
        }

        $groupsDefinition = $container->getDefinition(GroupsByCustomConfiguration::class);
        $groupsDefinition->setArgument(0, $config['items']);
        $groupsDefinition->setArgument(1, $tagsColorConfig);

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
