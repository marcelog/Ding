<?php

namespace Ding\Aspect\Interceptor;
use Ding\Aspect\MethodInvocation;

class Dispatcher implements IExceptionInterceptor, IMethodInterceptor
{
    private $_methodsIntercepted;
    
    public function addMethodInterceptor(
        $method, IMethodInterceptor $interceptor
    ) {
        $this->_methodsIntercepted[$method][] = $interceptor;
    }

    public function getInterceptors($method)
    {
        if (!isset($this->_methodsIntercepted[$method])) {
            return false;
        }
        return $this->_methodsIntercepted[$method];
    }
    public function invokeException(MethodInvocation $invocation)
    {
        
    }
    
    public function invoke(MethodInvocation $invocation)
    {
        $interceptors = $this->getInterceptors($invocation->getMethod());
        if ($interceptors != false) {
            $total = count($interceptors);
            $invocationChain = $invocation; 
            for ($i = 0; $i < $total; $i++) {
                $newInvocation = new MethodInvocation(
                	get_class($interceptors[$i]), 'invoke',
                    array($invocationChain), $interceptors[$i]
                );
                $invocationChain = $newInvocation;
            }
            $invocationChain->proceed();
        } else {
            return $invocation->proceed();
        }
    }
    
    public function __construct()
    {
        $this->_methodsIntercepted = array();
    }
}