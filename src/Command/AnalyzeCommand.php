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

namespace PhpDA\Command;

use PhpDA\Parser\Strategy\StrategyInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @SuppressWarnings("PMD.CouplingBetweenObjects")
 */
class AnalyzeCommand extends Command
{
    public function __construct(private StrategyInterface $strategy, private ParameterBagInterface $parameterBag)
    {
        parent::__construct('analyze');
    }

    protected function configure()
    {
        $this
            ->addOption('config', mode: InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($externalConfig = $input->getOption('config')) {
                if (!is_readable($externalConfig)) {
                    throw new InvalidArgumentException(sprintf('Неудается прочитать конфиг %s', $externalConfig));
                }

                $configData = include_once $externalConfig;
                foreach ($configData as $key => $value) {
                    $this->parameterBag->set($key, $value);
                }
            }

            $this->strategy->execute();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            throw new \Exception('Execution failed', 2, $e);
        }
    }

}
