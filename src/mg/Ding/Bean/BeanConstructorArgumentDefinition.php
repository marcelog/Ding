<?php
/**
 * Bean constructor argument definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Bean;

/**
 * Bean constructor argument definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class BeanConstructorArgumentDefinition
{
    /**
     * Means this argument is a reference to another bean.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_BEAN = 0;
    
    /**
     * Means this argument is a literal value.
     * @var integer
     */
    const BEAN_CONSTRUCTOR_VALUE = 1;
        
    /**
     * Argument type.
     * @var integer
     */
    private $_type;
    
    /**
     * Returns value for this argument. This is a bean name (string) in the
     * case of an argument of type bean.
     * @var mixed
     */
    private $_value;
    
    /**
     * Returns argument value. This is a bean name (string) in the
     * case of an argument of type bean.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Returns true if this argument is a reference to another bean.
     * 
     * @return boolean
     */
    public function isBean()
    {
        return $this->getType() == self::BEAN_CONSTRUCTOR_BEAN;
    }
    
    /**
     * Returns type for this argument.
     * 
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }
    
    /**
     * Constructor.
     * 
     * @param integer $type  Argument type.
     * @param mixed   $value Argument value.
     * 
     * @return void
     */
    public function __construct($type, $value)
    {
        $this->_type = $type;
        $this->_value = $value;
    }
}
