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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
namespace Ding\Aspect\Interceptor;
use Ding\Reflection\IReflectionFactoryAware;
use Ding\Reflection\IReflectionFactory;
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class DispatcherImpl implements IDispatcher, IReflectionFactoryAware
{
    /**
     * A ReflectionFactory implementation
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * Associated array for methods intercepted.
     * @var object[]
     */
    private $_methodsIntercepted;

    /**
     * Associated array for methods intercepted for exceptions.
     * @var object[]
     */
    private $_methodsExceptionIntercepted;

    /**
     * Interceptor classes.
     * @var string[]
     */
    private $_interceptorClasses;

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect\Interceptor.IDispatcher::hasMethodsIntercepted()
     */
    public function hasMethodsIntercepted()
    {
        return
            count($this->_methodsExceptionIntercepted) > 0
            || count($this->_methodsIntercepted) > 0
        ;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect\Interceptor.IDispatcher::getMethodsIntercepted()
     */
    public function getMethodsIntercepted()
    {
        return array_keys(array_merge(
            $this->_methodsExceptionIntercepted,
            $this->_methodsIntercepted
        ));
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect\Interceptor.IDispatcher::addMethodInterceptor()
     */
    public function addMethodInterceptor($method, $interceptor, $interceptorMethod)
    {
        $this->_methodsIntercepted[$method][] = new AdviceDefinition(
            $method, $interceptor, $interceptorMethod
        );
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect\Interceptor.IDispatcher::addExceptionInterceptor()
     */
    public function addExceptionInterceptor($method, $interceptor, $interceptorMethod)
    {
        $this->_methodsExceptionIntercepted[$method][] = new AdviceDefinition(
            $method, $interceptor, $interceptorMethod
        );
    }

    /**
     * Returns interceptors for a given method name or false if none was set.
     *
     * @param string $method Method to check for.
     *
     * @return object[]
     */
    public function getInterceptors($method)
    {
        if (!isset($this->_methodsIntercepted[$method])) {
            return false;
        }
        return $this->_methodsIntercepted[$method];
    }

    /**
     * Returns exception interceptors for a given method name or
     * false if none was set.
     *
     * @param string $method Method to check for.
     *
     * @return object[]
     */
    public function getExceptionInterceptors($method)
    {
        if (!isset($this->_methodsExceptionIntercepted[$method])) {
            return false;
        }
        return $this->_methodsExceptionIntercepted[$method];
    }

    /**
     * Will chain and return the result of the chained call of interceptors.
     *
     * @param MethodInvocation $invocation   Original invocation to preserve.
     * @param object[]         $interceptors Array of interceptors.
     *
     * @return mixed
     */
    private function _callInterceptors(
        MethodInvocation $invocation, array $interceptors
    ) {
        $total = count($interceptors) - 1;
        $invocationChain = $invocation;
        for ($i = $total; $i >= 0; $i--) {
            $newInvocation = new MethodInvocation(
                get_class($interceptors[$i]->getInterceptor()),
                $interceptors[$i]->getInterceptorMethod(),
                array($invocationChain),
                $interceptors[$i]->getInterceptor(),
                $this->reflectionFactory,
                $invocation
            );
            $invocationChain = $newInvocation;
        }
        return $invocationChain->proceed();
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
        $interceptors = $this->getExceptionInterceptors($method = $invocation->getMethod());
        if ($interceptors != false && !empty($interceptors)) {
            return $this->_callInterceptors($invocation, $interceptors);
        }
        throw $invocation->getException();
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
            return $this->_callInterceptors($invocation, $interceptors);
        }
        return $invocation->proceed();
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_methodsIntercepted = array();
        $this->_methodsExceptionIntercepted = array();
    }
}