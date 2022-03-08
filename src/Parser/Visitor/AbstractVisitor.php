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
use PhpDA\Parser\Filter;
use PhpDA\Parser\Visitor\Feature;
use PhpDA\Plugin\ConfigurableInterface;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;

/**
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 */
abstract class AbstractVisitor extends NodeVisitorAbstract implements AdtAwareInterface, ConfigurableInterface
{
    private ?Adt $adt = null;
    private ?Filter\NodeNameInterface $nodeNameFilter = null;

    public function setOptions(array $options)
    {
        $this->getNodeNameFilter()->setOptions($options);
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
     * @param Filter\NodeNameInterface $nodeNameFilter
     */
    public function setNodeNameFilter(Filter\NodeNameInterface $nodeNameFilter)
    {
        $this->nodeNameFilter = $nodeNameFilter;
    }

    public function getNodeNameFilter(): Filter\NodeNameInterface
    {
        if (!$this->nodeNameFilter instanceof Filter\NodeNameInterface) {
            $this->setNodeNameFilter(new Filter\NodeName);
        }

        return $this->nodeNameFilter;
    }

    /**
     * @param Node\Name $target
     * @param Node      $source
     */
    private function exchange(Node\Name $target, Node $source)
    {
        $attributes = $source->getAttributes();
        foreach ($attributes as $attr => $value) {
            $target->setAttribute($attr, $value);
        }
    }

    protected function filter(Node\Name $name): ?Node\Name
    {
        $raw = clone $name;

        if ($name = $this->getNodeNameFilter()->filter($name)) {
            $this->exchange($name, $raw);
        }

        return $name;
    }

    protected function collect(Node\Name $name, Node $node = null): void
    {
        if ($name = $this->filter($name)) {
            if (!is_null($node)) {
                $this->exchange($name, $node);
            }
            $adtMutator = $this->getAdtMutator();
            $this->getAdt()->$adtMutator($name);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function getAdtMutator(): string
    {
        if ($this->isDeclaredNamespaceCollector()) {
            return 'setDeclaredNamespace';
        }

        if ($this->isUsedNamespaceCollector()) {
            return 'addUsedNamespace';
        }

        if ($this->isUnsupportedNamespaceCollector()) {
            return 'addUnsupportedStmt';
        }

        if ($this->isNamespacedStringCollector()) {
            return 'addNamespacedString';
        }

        throw new RuntimeException(
            sprintf(
                'Visitor \'%s\' must implement '
                . 'PhpDA\\Parser\\Visitor\Feature\\DeclaredNamespaceCollectorInterface'
                . ' or PhpDA\\Parser\\Visitor\Feature\\UsedNamespaceCollectorInterface'
                . ' or PhpDA\\Parser\\Visitor\Feature\\NamespacedStringCollectorInterface'
                . ' or PhpDA\\Parser\\Visitor\Feature\\UnsupportedNamespaceCollectorInterface',
                get_class($this)
            )
        );
    }

    private function isDeclaredNamespaceCollector(): bool
    {
        return $this instanceof Feature\DeclaredNamespaceCollectorInterface;
    }

    private function isUsedNamespaceCollector(): bool
    {
        return $this instanceof Feature\UsedNamespaceCollectorInterface;
    }

    private function isUnsupportedNamespaceCollector(): bool
    {
        return $this instanceof Feature\UnsupportedNamespaceCollectorInterface;
    }

    private function isNamespacedStringCollector(): bool
    {
        return $this instanceof Feature\NamespacedStringCollectorInterface;
    }
}
