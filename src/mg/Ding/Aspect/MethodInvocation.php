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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
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
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
use Ding\Reflection\ReflectionFactory;

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
     * execution.
     *
     * @todo Performance: Remove new ReflectionMethod here
     *
	 * @return void
     */
    public function proceed()
    {
        $target = ReflectionFactory::getMethod($this->_class, $this->_method);
        if (!$target->isPublic()) {
            $target->setAccessible(true);
        }
        $result = $target->invokeArgs($this->_object, $this->_args);
        $this->setResult($result);
        return $result;
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
     * Changes (updates) the result for the execution of the aspected method.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setResult($value)
    {
        $this->_result = $value;
    }

    /**
     * Returns the result from executing the aspected method.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
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
     * Standard function, you know the drill..
     *
     * @return string
     */
    public function __toString()
    {
        return
            '['
            . __CLASS__
            . ' Class: ' . $this->getClass()
            . ' Method: ' . $this->getMethod()
            . ' Args: ' . print_r($this->getArguments(), true)
            . ' result: ' . $this->getResult()
            . ']'
        ;
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
     * @param MethodInvocation &$invoke In a chained aspect call, this will
     * be the access to the original (aspected) method call.
     *
     * @see MethodInvocation::getOriginalInvocation()
     * @return void
     */
    public function __construct(
        $class, $method, $args, $object,
        MethodInvocation &$invoke = null
    ) {
        $this->_class = $class;
        $this->_method = $method;
        $this->_args = $args;
        $this->_object = $object;
        $this->_originalInvocation = $invoke;
    }
}
