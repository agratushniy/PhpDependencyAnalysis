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

namespace PhpDA\Parser;

use PhpDA\Entity\Analysis;
use PhpDA\Entity\AnalysisCollection;
use PhpParser\Parser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

class SourceDirAnalyzer implements AnalyzerInterface
{
    public function __construct(
        private Parser $parser,
        private AdtTraverser $adtTraverser,
        private NodeTraverser $nodeTraverser,
        private Logger $logger,
        private Finder $finder
    ) {
    }

    public function analyze(): AnalysisCollection
    {
        $analysisCollection = new AnalysisCollection();

        foreach ($this->finder->getIterator() as $file) {
            /** @var SplFileInfo $file */
            $analysisCollection->attach($this->analyzeFile($file));
        }

        return $analysisCollection;
    }

    private function analyzeFile(SplFileInfo $file): Analysis
    {
        $analysis = new Analysis($file);

        try {
            if ($stmts = $this->parser->parse($file->getContents())) {
                $this->adtTraverser->bindFile($file);
                $adtStmts = $this->adtTraverser->getAdtStmtsBy($stmts);


                foreach ($adtStmts as $node) {
                    $this->nodeTraverser->setAdt($analysis->createAdt());
                    $this->nodeTraverser->traverse([$node]);
                }
            }
        } catch (Throwable $error) {
            $this->logger->error($error->getMessage(), [$file]);
        }

        return $analysis;
    }

    public function setupVisitors(array $visitors): self
    {
        foreach ($visitors as $visitor) {
            $this->nodeTraverser->addVisitor($visitor);
        }

        return $this;
    }

    public function configureScanner(string $source, string $filePattern, array $ignore = []): self
    {
        $this->finder
            ->files()
            ->name($filePattern)
            ->in($source)
            ->exclude($ignore)
            ->sortByName()
        ;

        return $this;
    }
}
