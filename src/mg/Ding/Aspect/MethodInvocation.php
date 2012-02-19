<?php
/**
 * Used as an argument to invoke the advice, so you can have all the details
 * about the invoked (aspected) method. In other words, your advice will be
 * invoked with an instance of this class as an argument.
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
 * Used as an argument to invoke the advice, so you can have all the details
 * about the invoked (aspected) method. In other words, your advice will be
 * invoked with an instance of this class as an argument.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
use Ding\Reflection\IReflectionFactory;

class MethodInvocation
{
    /**
     * Class name for the invoked (and aspected) method.
     * @var string
     */
    private $_class;

    /**
     * Name for the invoked (and aspected) method.
     * @var string
     */
    private $_method;

    /**
     * Arguments used to invoke the aspected method.
     * @var array
     */
    private $_args;

    /**
     * Aspected method result.
     * @var mixed
     */
    private $_result;

    /**
     * Exception thrown by the target method.
     */
    private $_exception;

    /**
     * ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * Original invocation.
     * In the case of a chained aspect call, getArguments() and alike, will
     * give you information about the calling aspect, and not the method
     * aspected itself (unless you are the last one in the chain). In order
     * to access information for the original request
     * @see MethodInvocation::getOriginalInvocation()
     * @var MethodInvocation
     */
    private $_originalInvocation;

    /**
     * This invocation will be called onto this object.
     * @var object
     */
    private $_object;

    /**
     * Returns information about the original invocation to the (aspected)
     * method. Will return itself as the original invocation if none was set
     * at construction time.
     *
     * @see MethodInvocation::$_originalInvocation
     *
     * @return MethodInvocation
     */
    public function getOriginalInvocation()
    {
        return
            $this->_originalInvocation == null
            ? $this
            : $this->_originalInvocation
        ;
    }

    /**
     * Call this one *from* your aspect, in order to proceed with the
     * execution. If you pass any arguments to this method, they will override
     * the original arguments when proceeding to the call.
     *
	 * @return void
     */
    public function proceed()
    {
        $target = $this->_reflectionFactory->getMethod($this->_class, $this->_method);
        if (!$target->isPublic()) {
            $target->setAccessible(true);
        }
        $arguments = func_get_args();
        if (empty($arguments)) {
            $arguments = $this->_args;
        }
        $this->_result = $target->invokeArgs($this->_object, $arguments);
        return $this->_result;
    }

    /**
     * If the target method throws an exception, you can get it here.
     *
	 * @return Exception
     */
    public function getException()
    {
        return $this->_exception;
    }

    /**
     * Changes (updates) the exception for the execution of the aspected method.
     *
     * @param Exception $value
     *
     * @return void
     */
    public function setException(\Exception $exception)
    {
        $this->_exception = $exception;
    }

    /**
     * Returns class name for the executed method.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Returns name for the executed method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Returns the target object for this method
     *
     * @return object
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Returns arguments for the executed method.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_args;
    }

    /**
     * Constructor.
     *
     * @param string           $class   Class for the aspected method invoked.
     * @param string           $method  Aspected method invoked.
     * @param array            $args    Arguments used to invoke the aspected
     * method.
     * @param mixed            $result  Result from the execution of the
     * aspected method.
     * @param object           $object  Target invocation object for method.
     * @param IReflectionFactory $reflectionFactory Reflection Factory to use.
     * @param MethodInvocation &$invoke In a chained aspect call, this will
     * be the access to the original (aspected) method call.
     *
     * @see MethodInvocation::getOriginalInvocation()
     * @return void
     */
    public function __construct(
        $class, $method, $args, $object, IReflectionFactory $reflectionFactory,
        MethodInvocation &$invoke = null
    ) {
        $this->_class = $class;
        $this->_method = $method;
        $this->_args = $args;
        $this->_object = $object;
        $this->_originalInvocation = $invoke;
        $this->_reflectionFactory = $reflectionFactory;
    }
}
