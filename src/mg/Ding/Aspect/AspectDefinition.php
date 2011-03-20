<?php
/**
 * This class is used when reading the bean definition. Aspects will be
 * constructed and applyed using this information, you may thing of this as
 * some kind of Aspect DTO created somewhere else and used by the container to
 * assemble the final bean.
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
 * This class is used when reading the bean definition. Aspects will be
 * constructed and applyed using this information, you may thing of this as
 * some kind of Aspect DTO created somewhere else and used by the container to
 * assemble the final bean.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class AspectDefinition
{
    /**
     * This kind of aspect will be run before the method call.
     * @var integer
     */
    const ASPECT_METHOD = 0;

    /**
     * This kind of aspect will be run when the method throws an uncatched
     * exception.
     * @var integer
     */
    const ASPECT_EXCEPTION = 1;

    /**
     * Aspect name.
     * @var string
     */
    private $_name;

    /**
     * Target aspected methods.
     * @var string[]
     */
    private $_pointcuts;

    /**
     * Aspect bean name.
     * @var string
     */
    private $_beanName;

    /**
     * Aspect type (or when the advice should be invoked).
     * @var integer
     */
    private $_type;

    /**
     * Regular expression for this aspect (global).
     * @var string
     */
    private $_expression;

    /**
     * Sets the expression for this aspect.
     *
     * @param string $expression Regular expression to set.
     *
     * @return void
     */
    public function setExpression($expression)
    {
        $this->_expression = $expression;
    }

    /**
     * Returns the expression for this aspect.
     *
     * @return string
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * Returns pointcut names.
     *
     * @return string[]
     */
    public function getPointcuts()
    {
        return $this->_pointcuts;
    }

    /**
     * Sets the pointcuts for this aspect.
     *
     * @param string[] $pointcuts Pointcut definition.
     *
     * @return void
     */
    public function setPointcuts($pointcuts)
    {
        $this->_pointcuts = $pointcuts;
    }

    /**
     * Sets the type for this aspect.
     *
     * @param integer $type Interceptor type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Returns advice type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns bean name.
     *
     * @return string
     */
    public function getBeanName()
    {
        return $this->_beanName;
    }

    /**
     * Sets the aspect bean name.
     *
     * @param string $name Bean name for this aspect.
     *
     * @return void
     */
    public function setBeanName($name)
    {
        $this->_beanName = $name;
    }

    /**
     * Sets aspect name.
     *
     * @param string $name New aspect name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns aspect name.
     *
	 * @return string
     */
    public function getName()
    {
        return $this->_name;
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
            . ' Pointcuts: ' . $this->getPointcuts()
            . ' Type: ' . intval($this->getType())
            . ' Aspect: ' . $this->getBeanName()
            . ' Expression: ' . $this->getExpression()
            . ']'
        ;
    }

    /**
     * Constructor.
     *
     * @param string   $name       Aspect name.
     * @param string[] $pointcuts  Pointcut names.
     * @param integer  $type       Aspect type (see this class constants).
     * @param string   $beanName   Aspect bean name.
     * @param string   $expression Regular expression for this aspect.
     *
     * @return void
     */
    public function __construct($name, $pointcuts, $type, $beanName, $expression)
    {
        $this->_name = $name;
        $this->_pointcuts = $pointcuts;
        $this->_beanName = $beanName;
        $this->_type = $type;
        $this->_expression = $expression;
    }
}