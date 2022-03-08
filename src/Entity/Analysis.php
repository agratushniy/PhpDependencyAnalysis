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

namespace PhpDA\Entity;

use Symfony\Component\Finder\SplFileInfo;

class Analysis
{
    private array $adts = [];

    /**
     * @param SplFileInfo $file
     */
    public function __construct(private SplFileInfo $file)
    {
    }

    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    public function createAdt(): Adt
    {
        $adt = new Adt;
        $this->adts[] = $adt;

        return $adt;
    }

    /**
     * @return Adt[]
     */
    public function getAdts(): array
    {
        return $this->adts;
    }
}
