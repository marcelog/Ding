<?php
/**
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Annotation
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
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
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Annotation
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class Annotation
{
    /**
     * Annotation name.
     * @var string
     */
    private $_name;

    /**
     * Annotation options.
     * @var array
     */
    private $_options;

    public function __sleep()
    {
        return array('_name', '_options');
    }

    /**
     * Returns annotation name.
     *
	 * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    public function getOptionValues($name)
    {
        if (!$this->hasOption($name)) {
            throw new AnnotationException("Unknown option: $name");
        }
        return $this->_options[$name];
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function addOption($name, $value)
    {
        $name = strtolower($name);
        if (!$this->hasOption($name)) {
            $this->_options[$name] = array();
        }
        $this->_options[$name][] = $value;
    }

    public function hasOption($name)
    {
        $name = strtolower($name);
        return isset($this->_options[$name]);
    }

    public function getOptionSingleValue($name)
    {
        $values = $this->getOptionValues($name);
        return array_shift($values);
    }
    /**
     * Constructor.
     *
     * @param string $name Annotation name.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->_name = strtolower($name);
        $this->_options = array();
    }
}