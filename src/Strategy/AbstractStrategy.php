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

use Fhaculty\Graph\Graph;
use PhpDA\Command\MessageInterface as Message;
use PhpDA\Config;
use PhpDA\HasOutputInterface;
use PhpDA\Layout;
use PhpDA\Parser\AnalyzerInterface;
use PhpDA\Parser\Filter\NamespaceFilterInterface;
use PhpDA\Plugin\LoaderInterface;
use PhpDA\Reference\ValidatorInterface;
use PhpDA\Writer\AdapterInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class AbstractStrategy implements StrategyInterface, HasOutputInterface
{
    /** @var Config */
    private $config;

    /** @var OutputInterface */
    private $output;

    /** @var integer */
    private $fileCnt = 0;

    /** @var AdapterInterface */
    private $writeAdapter;

    /** @var Layout\BuilderInterface */
    private $graphBuilder;

    /** @var LoaderInterface */
    private $pluginLoader;

    /** @var string */
    private $layoutLabel = '';

    /**
     * @param Finder                  $finder
     * @param AnalyzerInterface       $analyzer
     * @param Layout\BuilderInterface $graphBuilder
     * @param AdapterInterface        $writeAdapter
     * @param LoaderInterface         $loader
     */
    public function __construct(
        protected array $visitors,
        protected Finder $finder,
        protected AnalyzerInterface $analyzer,
        Layout\BuilderInterface $graphBuilder,
        AdapterInterface $writeAdapter,
        LoaderInterface $loader,
        protected array $options
    ) {
        $this->graphBuilder = $graphBuilder;
        $this->writeAdapter = $writeAdapter;
        $this->pluginLoader = $loader;


        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    private function initFinder()
    {
        $this->getFinder()
             ->files()
             ->name($this->options['filePattern'])
             ->in($this->options['source'])
             ->sortByName();

        if ($ignores = $this->options['ignore'] ?? null) {
            $this->getFinder()->exclude($ignores);
        }

        $this->fileCnt = $this->getFinder()->count();
    }


    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Finder
     */
    protected function getFinder()
    {
        return $this->finder;
    }

    /**
     * @return OutputInterface
     */
    protected function getOutput()
    {
        return $this->output;
    }

    /**
     * @return AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->writeAdapter;
    }

    /**
     * @return Layout\BuilderInterface
     */
    protected function getGraphBuilder()
    {
        return $this->graphBuilder;
    }

    public function execute(): bool
    {
        $this->initFinder();

        if ($this->fileCnt < 1) {
            $this->getOutput()->writeln(Message::NOTHING_TO_PARSE . PHP_EOL);
            return true;
        }

        //$this->bindNamespaceFilterToVisitorOptions();

        $this->analyzer->setupVisitors($this->visitors);

        $progressHelper = $this->createProgressHelper();
        $progressHelper->start();
        $this->iterateFiles($progressHelper);
        $progressHelper->finish();

        $this->writeAnalysis();
        $this->getOutput()->writeln(PHP_EOL . Message::DONE . PHP_EOL);

        $this->writeAnalysisFailures();

        return $this->analyzer->getLogger()->isEmpty() && !$this->getGraphBuilder()->hasViolations();
    }

    /**
     * @return ProgressBar
     */
    private function createProgressHelper()
    {
        $progress = new ProgressBar($this->getOutput(), $this->fileCnt);
        $progress->setFormat(Message::PROGRESS_DISPLAY);

        if ($this->fileCnt > 5000) {
            $progress->setRedrawFrequency(100);
        }

        return $progress;
    }


    private function iterateFiles(ProgressBar $progressHelper): void
    {
        foreach ($this->getFinder()->getIterator() as $file) {
            /** @var SplFileInfo $file */
            if (OutputInterface::VERBOSITY_VERBOSE <= $this->getOutput()->getVerbosity()) {
                $progressHelper->clear();
                $this->getOutput()->writeln("\x0D" . $file->getRealPath());
                $progressHelper->display();
            }
            $this->analyzer->analyze($file);
            $progressHelper->advance();
        }
    }

    private function writeAnalysis()
    {
        $this->getOutput()->writeln(
            PHP_EOL . PHP_EOL . sprintf(Message::WRITE_GRAPH_TO, $this->options['target'])
        );

        $this->getWriteAdapter()
             ->write($this->createGraph())
             ->with('PhpDA\Writer\Strategy\Svg')
             ->to('phpda.svg');
    }

    /**
     * @return Graph
     */
    private function createGraph()
    {
        /*if ($this->getConfig()->hasVisitorOptionsForAggregation()) {
            $layout = new Layout\Aggregation($this->layoutLabel);
        } else {
            $layout = new Layout\Standard($this->layoutLabel);
        }*/

        $layout = new Layout\Standard($this->layoutLabel);

        $graphBuilder = $this->getGraphBuilder();
        $graphBuilder->setLogEntries($this->analyzer->getLogger()->getEntries());
        $graphBuilder->setLayout($layout);
        $graphBuilder->setGroupLength($this->options['groupLength']);
        $graphBuilder->setAnalysisCollection($this->analyzer->getAnalysisCollection());

        /*if ($referenceValidator = $this->loadReferenceValidator()) {
            $graphBuilder->setReferenceValidator($referenceValidator);
        }*/

        return $graphBuilder->create()->getGraph();
    }

    /**
     * @throws RuntimeException
     * @return ValidatorInterface|null
     */
    private function loadReferenceValidator()
    {
        $referenceValidator = null;

        if ($fqcn = $this->getConfig()->getReferenceValidator()) {
            $referenceValidator = $this->pluginLoader->get($fqcn);
            if (!$referenceValidator instanceof ValidatorInterface) {
                throw new RuntimeException(
                    sprintf('ReferenceValidator \'%s\' must implement PhpDA\\Reference\\ValidatorInterface', $fqcn)
                );
            }
        }

        return $referenceValidator;
    }

    /**
     * @throws RuntimeException
     */
    private function bindNamespaceFilterToVisitorOptions()
    {
        if ($fqcn = $this->getConfig()->getNamespaceFilter()) {
            $namespaceFilter = $this->pluginLoader->get($fqcn);
            if (!$namespaceFilter instanceof NamespaceFilterInterface) {
                throw new RuntimeException(
                    sprintf(
                        'NamespaceFilter \'%s\' must implement PhpDA\\Parser\\Filter\\NamespaceFilterInterface',
                        $fqcn
                    )
                );
            }
            $this->getConfig()->setGlobalVisitorOption('namespaceFilter', $namespaceFilter);
        }
    }

    private function writeAnalysisFailures()
    {
        $logger = $this->analyzer->getLogger();
        if (!$logger->isEmpty()) {
            $this->getOutput()->writeln(Message::PARSE_LOGS);
            $this->getOutput()->writeln($logger->toString());
        }
    }
}
