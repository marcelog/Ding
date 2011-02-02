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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
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
     * Target aspected method.
     * @var string
     */
    private $_pointcut;

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
     * Returns pointcut name.
     *
     * @return string
     */
    public function getPointcut()
    {
        return $this->_pointcut;
    }

    /**
     * Sets the pointcut for this aspect.
     *
     * @param string $pointcut Pointcut definition.
     *
     * @return void
     */
    public function setPointcut($pointcut)
    {
        $this->_pointcut = $pointcut;
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
     * Standard function, you know the drill..
     *
     * @return string
     */
    public function __toString()
    {
        return
            '['
            . __CLASS__
            . ' Pointcut: ' . $this->getPointcut()
            . ' Type: ' . intval($this->getType())
            . ' Aspect: ' . $this->getBeanName()
            . ']'
        ;
    }

    /**
     * Constructor.
     *
     * @param string  $pointcut Pointcut name.
     * @param integer $type     Aspect type (see this class constants).
     * @param string  $beanName Aspect bean name.
     *
     * @return void
     */
    public function __construct($pointcut, $type, $beanName)
    {
        $this->_pointcut = $pointcut;
        $this->_beanName = $beanName;
        $this->_type = $type;
    }
}