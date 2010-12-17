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
            . ' Aspect: ' . $this->getBeanName()
            . ']'
        ;
    }
    
    /**
     * Constructor.
     * 
     * @param string $pointcut Pointcut name.
     * @param string $advice   Advice name (method name).
     * @param string $beanName Aspect bean name.
     * 
	 * @return void
     */
    public function __construct($pointcut, $advice, $beanName)
    {
        $this->_pointcut = $pointcut;
        $this->_beanName = $beanName;
        $this->_advice = $advice;
    }
}