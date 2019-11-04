<?php
/**
 * Copyright (C) 2012 Louis-Philippe Huberdeau
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace izytechAB\Neo4j\Meta;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;

class Repository
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * @var array
     */
    private $metas = array();

    /**
     * @param Reader $annotationReader
     */
    function __construct(Reader $annotationReader = null)
    {
        if ($annotationReader instanceof Reader) {
            $this->reader = $annotationReader;
        } else {
            $this->reader = new AnnotationReader;
        }
    }

    /**
     * @param $className
     * @return Entity
     */
    function fromClass($className)
    {
        if (!isset($this->metas[$className])) {
            $this->metas[$className] = Entity::fromClass($this->reader, $className, $this);
        }

        return $this->metas[$className];
    }
}

