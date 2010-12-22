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
     * Returns true if this bean has mapped aspects.
     * 
     * @return boolean
     */
    public function hasAspects()
    {
        return count($this->getAspects()) > 0;
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
    
    /**
     * Returns bean type (scope). See this class constants.
     * 
     * @return integer
     */
    public function getScope()
    {
        return $this->_scope;
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

    /**
     * Returns bean class.
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
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

    /**
     * Returns arguments for this bean.
     * 
     * @return BeanConstructorArgumentDefinition[]
     */
    public function getArguments()
    {
        return $this->_constructorArgs;
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

    /**
     * Factory bean, false if none was set.
     * 
     * @return string
     */
    public function getFactoryBean()
    {
        return $this->_factoryBean;
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
     * @param BeanPropertyDefinition[] $properties    Bean properties
     * definitions.
     * @param AspectDefinition[]       $aspects       Aspects definitions.
     * @param BeanConstructorArgumentDefinition[] $arguments Constructor args.
     * 
     * @return void
     */
    public function __construct(
        $name, $class, $scope, $factoryMethod, $factoryBean, array $properties,
        array $aspects, array $arguments
    ) {
        $this->_name = $name;
        $this->_class = $class;
        $this->_scope = $scope;
        $this->_factoryMethod = $factoryMethod;
        $this->_factoryBean = $factoryBean;
        $this->_properties = $properties;
        $this->_aspects = $aspects;
        $this->_constructorArgs = $arguments;
    }
}
