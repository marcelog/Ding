<?php
namespace Ding;

/**
 * Bean property definition.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  component
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
 */

/**
 * Bean property definition.
 *
 * PHP Version 5
 *
 * @category ding
 * @package  component
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class BeanPropertyDefinition
{
    /**
     * This constant represents a property that is an integer, string, or any
     * other native type.
     * @var integer
     */
    const PROPERTY_SIMPLE = 0;

    /**
     * This constant represents a property that is another bean.
     * @var integer
     */
    const PROPERTY_BEAN = 1;

    /**
     * Property name
     * @var string
     */
    private $_name;

    /**
     * Property value (in the case of a bean property, this is the bean name).
     * @var string
     */
    private $_value;

    /**
     * Property type (see this class constants)
     * @var string
     */
    private $_type;
    
    /**
     * Returns property value (or bean name in the case of a bean property).
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }
    
    /**
     * Returns property name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns property type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
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
            . ' Name: ' . $this->getName()
            . ' Type: ' . intval($this->getType())
            . ' Value: ' . (string)$this->getValue()
            . ']'
        ;
    }
        
    /**
     * Constructor.
     * 
     * @param string  $name  Target property name.
     * @param integer $type  Target property type (See this class constants).
     * @param string  $value Target property value.
     * 
     * @return void
     */
    public function __construct($name, $type, $value)
    {
        $this->_name = $name;
        $this->_type = $type;
        $this->_value = $value;
    }
}
