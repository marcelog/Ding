<?php
/**
 * Interface for an aspect dispatcher.
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

use Ding\Aspect\MethodInvocation;
/**
 * Interface for an aspect dispatcher.
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
interface IDispatcher
{
    /**
     * Adds a method interceptor to the chain of a given method name.
     *
     * @param string $method            Method name.
     * @param object $interceptor       Interceptor object.
     * @param string $interceptorMethod Interceptor method name.
     *
     * @return void
     */
    public function addMethodInterceptor($method, $interceptor, $interceptorMethod);

    /**
     * Adds a method interceptor to the chain of the exception interceptors
     * for a given method name.
     *
     * @param string $method            Method name.
     * @param object $interceptor       Interceptor object.
     * @param string $interceptorMethod Interceptor method name.
     *
     * @return void
     */
    public function addExceptionInterceptor($method, $interceptor, $interceptorMethod);

    /**
     * The proxy will call this method when an aspected method throws an
     * exception.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     *
     * @return void
     */
    public function invokeException(MethodInvocation $invocation);

    /**
     * The proxy will call this method when an aspected method is called.
     *
     * @param MethodInvocation $invocation Method invocation "descriptor".
     *
     * @return void
     */
    public function invoke(MethodInvocation $invocation);
    /**
     * Returns all methods as an array of string, that are intercepted by
     * this dispatcher.
     *
	 * @return string[]
     */
    public function getMethodsIntercepted();
    /**
     * Returns true if this dispatcher has any methods intercepted.
     *
     * @return boolean
     */
    public function hasMethodsIntercepted();
}
