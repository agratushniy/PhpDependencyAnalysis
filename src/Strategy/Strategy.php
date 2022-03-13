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

namespace PhpDA\Strategy;

use PhpDA\GraphBuilder;
use PhpDA\Mutator\GraphMutatorInterface;
use PhpDA\Parser\AnalyzerInterface;
use PhpDA\Writer\WriterInterface;

class Strategy implements StrategyInterface
{
    public function __construct(
        protected array             $visitors,
        protected AnalyzerInterface $analyzer,
        protected GraphBuilder      $graphBuilder,
        protected WriterInterface   $graphWriter,
        protected array             $options,
        protected iterable          $graphMutators
    ) {
    }

    public function execute(): void
    {
        $analysisCollection = $this->analyzer
            ->setupVisitors($this->visitors)
            ->configureScanner($this->options['source'], $this->options['filePattern'], $this->options['ignore'] ?? [])
            ->analyze()
        ;

        //Исходный граф зависимостей
        $graph = $this->graphBuilder->build($analysisCollection);

        //Мутация графа
        foreach ($this->graphMutators as $graphMutator) {
            /**
             * @var GraphMutatorInterface $graphMutator
             */
            $graphMutator->mutate($graph);
        }

        //Мутированный граф передаем в распаковщик (json, svg ...)
        $this->graphWriter->writeGraphTo($graph, $this->options['target']);
    }
}
