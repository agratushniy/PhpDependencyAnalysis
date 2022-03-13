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

namespace PhpDA\Parser\Visitor;

use DomainException;
use PhpDA\Entity\Adt;
use PhpDA\Entity\AdtAwareInterface;
use PhpDA\Parser\Filter\NodeNameFilterInterface;
use PhpDA\Parser\NameTransformer\NodeNameTransformerInterface;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 */
abstract class AbstractVisitor extends NodeVisitorAbstract implements AdtAwareInterface
{
    private ?Adt $adt = null;

    public function __construct(
        private NodeNameFilterInterface      $namespaceFilter,
        private NodeNameTransformerInterface $nodeNameTransformer
    ) {
    }

    public function setAdt(Adt $adt)
    {
        $this->adt = $adt;
    }

    public function getAdt(): ?Adt
    {
        if (!$this->adt instanceof Adt) {
            throw new DomainException('Adt has not been set');
        }

        return $this->adt;
    }

    /**
     * @param Node\Name $target
     * @param Node $source
     */
    private function exchangeAttributes(Node\Name $target, Node $source)
    {
        $attributes = $source->getAttributes();
        foreach ($attributes as $attr => $value) {
            $target->setAttribute($attr, $value);
        }
    }

    protected function collect(Node\Name $namespace, Node $node = null): void
    {
        $copiedNs = clone $namespace;
        $transformedNamespace = $this->nodeNameTransformer->transform($copiedNs);

        if (!$this->namespaceFilter->filter($transformedNamespace)) {
            return;
        }

        if (!is_null($node)) {
            $this->exchangeAttributes($transformedNamespace, $node);
        }

        $this->addToAdt($transformedNamespace);
    }

    protected abstract function addToAdt(Node\Name $name): void;
}
