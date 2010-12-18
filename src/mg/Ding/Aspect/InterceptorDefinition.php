<?php
/**
 * This class is instantiated by the container. It will be used by the AOP
 * proxies to call the given advices from the given aspects (beans) whenever
 * the chosen target method is executed.
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
 * This class is instantiated by the container. It will be used by the AOP
 * proxies to call the given advices from the given aspects (beans) whenever
 * the chosen target method is executed.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class InterceptorDefinition
{
    /**
     * Method to be aspected.
     * @var ReflectionMethod
     */
    private $_targetMethod;

    /**
     * Advice to run.
     * @var ReflectionMethod
     */
    private $_interceptorMethod;

    /**
     * Instantiated bean.
     * @var object
     */
    private $_objectInterceptor;
    
    /**
     * Holds aspect definition.
     * @var AspectDefinition
     */
    private $_aspectDefinition;
    
    /**
     * Return reflected target method.
     * 
     * @return ReflectionMethod
     */
    public function getTargetMethod()
    {
        return $this->_targetMethod;
    }
    
    /**
     * Return reflected advice method.
     * 
     * @return ReflectionMethod
     */
    public function getInterceptorMethod()
    {
        return $this->_interceptorMethod;
    }
    
    /**
     * Return reflected bean object to be invoked.
     * 
     * @return object
     */
    public function getObjectInterceptor()
    {
        return $this->_objectInterceptor;
    }

    /**
     * Returns aspect definition.
     * 
	 * @return AspectDefinition
     */
    public function getAspectDefinition()
    {
        return $this->_aspectDefinition;
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
     * @param ReflectionMethod $targetMethod      Method to be aspected.
     * @param ReflectionMethod $interceptorMethod Advice to run.
     * @param Object           $object            Bean to be invoked.
     * @param AspectDefinition $aspectDefinition  Aspect definition.
     * 
     * @return void
     */
    public function __construct(
        \ReflectionMethod $targetMethod,
        \ReflectionMethod $interceptorMethod,
        $object,
        AspectDefinition $aspectDefinition
    ) {
        $this->_targetMethod = $targetMethod;
        $this->_interceptorMethod = $interceptorMethod;
        $this->_objectInterceptor = $object;        
        $this->_aspectDefinition = $aspectDefinition;
    }
}