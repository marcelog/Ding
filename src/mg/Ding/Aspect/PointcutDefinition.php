<?php
/**
 * A pointcut definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @version  SVN: $Id$
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
namespace Ding\Aspect;

/**
 * A pointcut definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class PointcutDefinition
{
    /**
     * Pointcut name/id.
     * @var string
     */
    private $_name;

    /**
     * Pointcut regular expression.
     * @var string
     */
    private $_expression;

    /**
     * Returns pointcut name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the pointcut name.
     *
     * @param string $name Pointcut name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns pointcut expression.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * Sets the pointcut expression.
     *
     * @param string $expression Pointcut expression.
     *
     * @return void
     */
    public function setExpression($expression)
    {
        $this->_expression= $expression;
    }

    /**
     * Standard function, you know the drill..
     *
     * @return string
     */
    public function __toString()
    {
        return
            '['
            . __CLASS__
            . ' Name: ' . $this->getName()
            . ' Expression: ' . $this->getExpression()
            . ']'
        ;
    }

    /**
     * Constructor.
     *
     * @param string  $name       Pointcut name.
     * @param integer $expression Pointcut expression.
     *
     * @return void
     */
    public function __construct($name, $expression)
    {
        $this->_name = $name;
        $this->_expression = $expression;
    }
}