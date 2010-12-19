<?php
/**
 * Aspect dispatcher implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Aspect\Interceptor;
use Ding\Aspect\MethodInvocation;

/**
 * Aspect dispatcher implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Aspect
 * @subpackage Interceptor
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class DispatcherImpl implements IDispatcher
{
    /**
     * Associated array for methods intercepted.
     * @var IMethodInterceptor[]
     */
    private $_methodsIntercepted;
    
    /**
     * Adds a method interceptor to the chain of a given method name.
     * 
     * @param string             $method      Method name.
     * @param IMethodInterceptor $interceptor Interceptor to call.
     * 
     * @return void
     */
    public function addMethodInterceptor(
        $method, IMethodInterceptor $interceptor
    ) {
        $this->_methodsIntercepted[$method][] = $interceptor;
    }

    /**
     * Returns interceptors for a given method name or false if none was set.
     * 
     * @param string $method Method to check for.
     * 
     * @return IMethodIntreceptor[]
     */
    public function getInterceptors($method)
    {
        if (!isset($this->_methodsIntercepted[$method])) {
            return false;
        }
        return $this->_methodsIntercepted[$method];
    }

    /**
     * The proxy will call this method when an aspected method throws an
     * exception.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     * 
     * @return void
     */
    public function invokeException(MethodInvocation $invocation)
    {
        
    }
    
    /**
     * The proxy will call this method when an aspected method is called.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     * 
     * @return void
     */
    public function invoke(MethodInvocation $invocation)
    {
        $interceptors = $this->getInterceptors($invocation->getMethod());
        if ($interceptors != false) {
            $total = count($interceptors) - 1;
            $invocationChain = $invocation; 
            for ($i = $total; $i >= 0; $i--) {
                $newInvocation = new MethodInvocation(
                	get_class($interceptors[$i]), 'invoke',
                    array($invocationChain), $interceptors[$i], $invocation
                );
                $invocationChain = $newInvocation;
            }
            return $invocationChain->proceed();
        } else {
            return $invocation->proceed();
        }
    }
    
    /**
     * Constructor.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_methodsIntercepted = array();
    }
}