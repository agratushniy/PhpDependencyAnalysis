<?php

declare(strict_types=1);

namespace PhpDA\Mutator;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use PhpDA\Entity\Group;
use PhpDA\Layout\LayoutBuilder;
use PhpDA\Layout\LayoutProviderInterface;
use PhpParser\Node\Name;

class GroupsByCustomConfiguration implements GraphMutatorInterface, LayoutBuilder
{
    /**
     * @var array|Group[]
     */
    private array $groups;

    public function __construct(
        array                           $groupsConfiguration,
        private array                   $tagsLayoutConfig,
        private LayoutProviderInterface $layoutProvider,
    ) {
        $this->groups = [
            Group::undefined(0)
        ];

        foreach ($groupsConfiguration as $k => $configuration) {
            $this->groups[] =
                new Group($k + 1, $configuration['title'], $configuration['tag'], $configuration['items']);
        }
    }

    public function mutate(Graph $graph): void
    {
        foreach ($graph->getVertices() as $vertex) {
            /**
             * @var Vertex $vertex
             */
            $groupId = $this->getIdFor(new Name($vertex->getId()));
            $vertex->setGroup($groupId);
        }
    }

    private function getIdFor(Name $className): int
    {
        foreach ($this->groups as $group) {
            if ($group->contains($className)) {
                return $group->getId();
            }
        }

        return 0;
    }

    private function findById(int $id): ?Group
    {
        foreach ($this->groups as $group) {
            if ($group->getId() === $id) {
                return $group;
            }
        }

        return null;
    }

    public function support(int $groupId): bool
    {
        return (bool)$this->findById($groupId);
    }

    public function buildLayout(int $groupId): string
    {
        $layout = '';

        if (!$group = $this->findById($groupId)) {
            return $layout;
        }

        $groupLayout = $this->layoutProvider->group();

        foreach ($this->tagsLayoutConfig as $tagConfig) {
            if ($tagConfig['name'] !== $group->getTag()) {
                continue;
            }

            $groupLayout['fillcolor'] = $tagConfig['color'];
        }

        foreach ($groupLayout as $attr => $val) {
            $layout .= PHP_EOL . $attr . '=' . GraphViz::escape($val) . ';';
        }

        return GraphViz::escape($group->getTitle()) . $layout;
    }
}
