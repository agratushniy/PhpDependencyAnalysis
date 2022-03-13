<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 Marco Muths
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace PhpDA\Writer\Strategy;

use Fhaculty\Graph\Attribute\AttributeAware;
use Fhaculty\Graph\Graph;
use PhpDA\Layout\GraphViz;
use PhpDA\Layout\LayoutProviderInterface;
use PhpDA\Writer\WriterInterface;

abstract class AbstractGraphViz implements WriterInterface
{
    public function __construct(protected GraphViz $graphViz, protected LayoutProviderInterface $layoutProvider, protected string $targetFilePath)
    {
    }

    public function write(Graph $graph): void
    {
        $this->bindAttributesBy($graph);

        file_put_contents($this->targetFilePath, $this->toString($graph), LOCK_EX);
    }

    private function bindAttributesBy(Graph $graph): void
    {
        $this->bindLayoutTo($graph, $this->layoutProvider->graph(), 'graphviz.graph.');

        foreach ($graph->getVertices() as $vertex) {
            $this->bindLayoutTo($vertex, $this->layoutProvider->vertex());
        }

        foreach ($graph->getEdges() as $edge) {
            $this->bindLayoutTo($edge, $this->layoutProvider->edge());
        }
    }

    private function bindLayoutTo(AttributeAware $attributeAware, array $layout, string $prefix = 'graphviz.')
    {
        foreach ($layout as $name => $attr) {
            $attributeAware->setAttribute($prefix . $name, $attr);
        }
    }

    abstract protected function toString(Graph $graph): string;
}
