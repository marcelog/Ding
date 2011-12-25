<?php
/**
 * A collection of annotations.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Ding\Annotation;

use Ding\Annotation\Exception\AnnotationException;

/**
 * A collection of annotations.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Collection
{
    private $_annotations = array();

    public function __sleep()
    {
        return array('_annotations');
    }

    public function contains($name)
    {
        $name = strtolower($name);
        return isset($this->_annotations[$name]);
    }

    public function getAll()
    {
        return $this->_annotations;
    }

    public function getSingleAnnotation($name)
    {
        $annotations = $this->getAnnotations($name);
        return array_shift($annotations);
    }

    public function getAnnotations($name)
    {
        $name = strtolower($name);
        if ($this->contains($name)) {
            return $this->_annotations[$name];
        }
        throw new AnnotationException("Unknown annotation: $name");
    }

    public function add(Annotation $annotation)
    {
        $name = strtolower($annotation->getName());
        if (!$this->contains($name)) {
            $this->_annotations[$name] = array();
        }
        $this->_annotations[$name][] = $annotation;
    }
}
