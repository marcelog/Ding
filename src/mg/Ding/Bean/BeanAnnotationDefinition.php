<?php
/**
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
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
namespace Ding\Bean;

/**
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class BeanAnnotationDefinition
{
    /**
     * Annotation name.
     * @var string
     */
    private $_name;

    /**
     * Annotation arguments.
     * @var array
     */
    private $_args;

    /**
     * Returns annotation name.
     *
	 * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns arguments for this annotation.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_args;
    }

    /**
     * Constructor.
     *
     * @param string $name Annotation name.
     * @param array  $args Annotation arguments.
     *
     * @return void
     */
    public function __construct($name, $args)
    {
        $this->_name = $name;
        $this->_args = $args;
    }
}