<?php
namespace Ding;

/**
 * This class is used when reading the bean definition. Aspects will be
 * constructed and applyed using this information, you may thing of this as
 * some kind of Aspect DTO created somewhere else and used by the container to
 * assemble the final bean.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

/**
 * This class is used when reading the bean definition. Aspects will be
 * constructed and applyed using this information, you may thing of this as
 * some kind of Aspect DTO created somewhere else and used by the container to
 * assemble the final bean.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class AspectDefinition
{
    /**
     * This kind of aspect will be run after the target method, only if the
     * target method did not throw an exception.
     * @var integer
     */
    const ASPECT_AFTER = 0;

    /**
     * This kind of aspect will be run before the target method.
     * @var integer
     */
    const ASPECT_BEFORE = 1;

    /**
     * This kind of aspect will be run after the target method, only in case of
     * an exception.
     * @var integer
     */
    const ASPECT_AFTERTHROW = 2;

    /**
     * This kind of aspect will be run before and after the target method.
     * @var integer
     */
    const ASPECT_AROUND = 3;

    /**
     * This kind of aspect will be run after the target method, regardless of
     * execution result (normal or exception).
     * @var integer
     */
    const ASPECT_AFTERFINALLY = 4;
    
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
     * Advice name to be executed.
     * @var string
     */
    private $_advice;
    
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
     * Returns advice name.
     * 
	 * @return string
     */
    public function getAdvice()
    {
        return $this->_advice;
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
            . ' Advice: ' . $this->getAdvice()
            . ' Type: ' . intval($this->getType())
            . ' Aspect: ' . $this->getBeanName()
            . ']'
        ;
    }
    
    /**
     * Constructor.
     * 
     * @param string  $pointcut Pointcut name.
     * @param string  $advice   Advice name (method name).
     * @param integer $type     Aspect type (see this class constants).
     * @param string  $beanName Aspect bean name.
     * 
	 * @return void
     */
    public function __construct($pointcut, $advice, $type, $beanName)
    {
        $this->_pointcut = $pointcut;
        $this->_beanName = $beanName;
        $this->_advice = $advice;
        $this->_type = $type;
    }
}