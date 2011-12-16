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
     * Returns aspect name.
     *
	 * @return string
     */
    public function getName()
    {
        return $this->_name;
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