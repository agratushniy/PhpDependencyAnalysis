<?php

declare(strict_types=1);

namespace PhpDA;

use Doctrine\Common\Collections\Collection;
use Fhaculty\Graph\Attribute\AttributeAware;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use PhpDA\Entity\Adt;
use PhpDA\Entity\Location;
use PhpParser\Node\Name;
use Symfony\Component\Finder\SplFileInfo;

final class GraphBuilder
{
    public function __construct(protected Graph $graph)
    {
    }

    public function build(Collection $analysisCollection): Graph
    {
        foreach ($analysisCollection->getIterator() as $analysis) {
            foreach ($analysis->getAdts() as $adt) {
                if (!$adt->hasDeclaredGlobalNamespace()) {
                    $this->createVertexAndEdgesBy($analysis->getFile(), $adt);
                }
            }
        }

        return $this->graph;
    }

    private function createVertexAndEdgesBy(SplFileInfo $file, Adt $adt): void
    {
        $adtRootName = $adt->getDeclaredNamespace();
        $adtRootVertex = $this->createVertexBy($adtRootName);

        $location = new Location($file, $adtRootName);
        $this->addLocationTo($adtRootVertex, $location);
        $adtRootVertex->setAttribute('adt', $adt->toArray());

        $this->createEdgesFor($adtRootVertex, $file, $adt->getMeta()->getImplementedNamespaces());
        $this->createEdgesFor($adtRootVertex, $file, $adt->getMeta()->getExtendedNamespaces());
        $this->createEdgesFor($adtRootVertex, $file, $adt->getMeta()->getUsedTraitNamespaces());
        $this->createEdgesFor($adtRootVertex, $file, $adt->getUsedNamespaces());
        $this->createEdgesFor($adtRootVertex, $file, $adt->getUnsupportedStmts());
        $this->createEdgesFor($adtRootVertex, $file, $adt->getNamespacedStrings());
    }

    private function createVertexBy(Name $name): Vertex
    {
        return $this->graph->createVertex($name->toString(), true);
    }

    private function createEdgesFor(Vertex $adtRootVertex, SplFileInfo $rootVertexFile, array $dependencies): void
    {
        foreach ($dependencies as $dependencyName) {
            $vertex = $this->createVertexBy($dependencyName);

            if ($adtRootVertex->getId() !== $vertex->getId()) {
                $edge = $this->createEdgeToAdtRootVertexBy($adtRootVertex, $vertex);
                $location = new Location($rootVertexFile, $dependencyName);
                $this->addLocationTo($edge, $location);
            }
        }
    }

    private function createEdgeToAdtRootVertexBy(Vertex $adtRootVertex, Vertex $vertex): Directed
    {
        foreach ($adtRootVertex->getEdges() as $edge) {
            /** @var Directed $edge */
            if ($edge->isConnection($adtRootVertex, $vertex)) {
                return $edge;
            }
        }

        return $adtRootVertex->createEdgeTo($vertex);
    }

    /**
     * @param AttributeAware $attributeAware
     * @param Location       $location
     */
    private function addLocationTo(AttributeAware $attributeAware, Location $location)
    {
        $locations = $attributeAware->getAttribute('locations', []);

        $key = base64_encode(serialize($location->toArray()));
        $locations[$key] = $location;

        $attributeAware->setAttribute('locations', $locations);
    }
}
