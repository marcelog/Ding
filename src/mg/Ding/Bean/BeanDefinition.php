<?php
/**
 * Bean Definition.
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
 * Bean Definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class BeanDefinition
{
    /**
     * Specifies scope prototype for beans, meaning that a new instance will
     * be returned every time.
     * @var integer
     */
    const BEAN_PROTOTYPE = 0;
    
    /**
     * Specifies scope singleton for beans, meaning that the same instance will
     * be returned every time.
     * @var integer
     */
    const BEAN_SINGLETON = 1;
    
    /**
     * Bean name
     * @var string
     */
    private $_name;

    /**
     * Bean class name.
     * @var string
     */
    private $_class;

    /**
     * Bean type (scope). See this class constants.
     * @var integer
     */
    private $_scope;

    /**
     * Properties to be di'ed to this bean.
     * @var BeanPropertyDefinition[]
     */
    private $_properties;

    /**
     * Aspects mapped to this bean.
     * @var AspectDefinition[]
     */
    private $_aspects;
    
    /**
     * Constructor arguments.
     * @var BeanConstructorArgumentDefinition[]
     */
    private $_constructorArgs;

    /**
     * Factory method name (if any). 
     * @var string
     */
    private $_factoryMethod;

    /**
     * Factory bean name (if any). 
     * @var string
     */
    private $_factoryBean;
    
    /**
     * Init method (if any).
     * @var string
     */
    private $_initMethod;
    
    /**
     * Destroy method (called when container is destroyed).
     * @var string
     */
    private $_destroyMethod;
    
    /**
     * Returns true if this bean has mapped aspects.
     * 
     * @return boolean
     */
    public function hasAspects()
    {
        return count($this->getAspects()) > 0;
    }
    
    public function setAspects(array $aspects)
    {
        $this->_aspects = $aspects;
    }
    
    /**
     * Returns aspects for this bean.
     * 
     * @return AspectDefinition[]
     */
    public function getAspects()
    {
        return $this->_aspects;
    }
    
    public function setScope($scope)
    {
        $this->_scope = $scope;
    }
    
    /**
     * Returns bean type (scope). See this class constants.
     * 
     * @return integer
     */
    public function getScope()
    {
        return $this->_scope;
    }
    
    public function setName($name)
    {
        $this->_name = $name;
    }
    
    /**
     * Returns bean name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    public function setClass($class)
    {
        $this->_class = $class;
    }
    
    /**
     * Returns bean class.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    public function setProperties(array $properties)
    {
        $this->_properties = $properties;
    }
    
    /**
     * Returns properties for this bean.
     * 
     * @return BeanPropertyDefinition[]
     */
    public function getProperties()
    {
        return $this->_properties;
    }

    public function setArguments(array $arguments)
    {
        $this->_constructorArguments = $arguments;
    }
    
    /**
     * Returns arguments for this bean.
     * 
     * @return BeanConstructorArgumentDefinition[]
     */
    public function getArguments()
    {
        return $this->_constructorArgs;
    }
    
    public function setFactoryMethod($factoryMethod)
    {
        $this->_factoryMethod = $factoryMethod;
    }
    /**
     * Factory method, false if none was set.
     * 
     * @return string
     */
    public function getFactoryMethod()
    {
        return $this->_factoryMethod;
    }

    public function setFactoryBean($factoryBean)
    {
        $this->_factoryBean = $factoryBean;
    }
    
    /**
     * Factory bean, false if none was set.
     * 
     * @return string
     */
    public function getFactoryBean()
    {
        return $this->_factoryBean;
    }

    public function setInitMethod($initMethod)
    {
        $this->_initMethod = $initMethod;
    }
    
    /**
     * Init method, false if none was set.
     * 
     * @return string
     */
    public function getInitMethod()
    {
        return $this->_initMethod;
    }

    public function setDestroyMethod($destroyMethod)
    {
        $this->_destroyMethod = $destroyMethod;
    }
    
    /**
     * Destroy method, false if none was set.
     * 
     * @return string
     */
    public function getDestroyMethod()
    {
        return $this->_destroyMethod;
    }
    
    /**
     * Constructor.
     * 
     * @param string                   $name          Bean name.
     * @param string                   $class         Bean class.
     * @param integer                  $scope         Bean type (scope). See
     * this class constants.
     * @param string                   $factoryMethod Factory method name or
     * false.
     * @param string                   $factoryBean   Factory bean name or
     * false.
     * @param string                   $initMethod    Init method.
     * @param string                   $destroyMethod Destroy method.
     * @param BeanPropertyDefinition[] $properties    Bean properties
     * definitions.
     * @param AspectDefinition[]       $aspects       Aspects definitions.
     * @param BeanConstructorArgumentDefinition[] $arguments Constructor args.
     * 
     * @return void
     */
    public function __construct(
        $name, $class, $scope, $factoryMethod, $factoryBean, $initMethod,
        $destroyMethod, array $properties, array $aspects, array $arguments
    ) {
        $this->_name = $name;
        $this->_class = $class;
        $this->_scope = $scope;
        $this->_factoryMethod = $factoryMethod;
        $this->_factoryBean = $factoryBean;
        $this->_initMethod = $initMethod;
        $this->_destroyMethod = $destroyMethod;
        $this->_properties = $properties;
        $this->_aspects = $aspects;
        $this->_constructorArgs = $arguments;
    }
}
