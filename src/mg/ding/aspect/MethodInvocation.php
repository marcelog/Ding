<?php
namespace Ding;

/**
 * Used as an argument to invoke the advice, so you can have all the details
 * about the invoked (aspected) method. In other words, your advice will be
 * invoked with an instance of this class as an argument.
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
 * Used as an argument to invoke the advice, so you can have all the details
 * about the invoked (aspected) method. In other words, your advice will be
 * invoked with an instance of this class as an argument.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  aspect
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
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
     * @param string $class  Class for the aspected method invoked.
     * @param string $method Aspected method invoked.
     * @param array  $args   Arguments used to invoke the aspected method.
     * @param mixed  $result Result from the execution of the aspected method.
     * 
	 * @return void
     */
    public function construct($class, $method, $args, $result)
    {
        $this->_class = $class;
        $this->_method = $method;
        $this->_args = $args;
        $this->_result = $result;
    }
}