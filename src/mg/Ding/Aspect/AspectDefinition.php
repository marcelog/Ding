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