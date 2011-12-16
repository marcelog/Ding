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
     * Target method to execute.
     * @var string
     */
    private $_method;

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
     * Returns the target method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Sets the target method.
	 *
     * @param string $method Sets the target method to execute.
     *
     * @return void
     */
    public function setMethod($method)
    {
        return $this->_method = $method;
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
     * Constructor.
     *
     * @param string $name       Pointcut name.
     * @param string $expression Pointcut expression.
     * @param string $method     Target method to execute.
     *
     * @return void
     */
    public function __construct($name, $expression, $method)
    {
        $this->_name = $name;
        $this->_expression = $expression;
        $this->_method = $method;
    }
}