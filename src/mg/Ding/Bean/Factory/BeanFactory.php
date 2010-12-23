<?php
/**
 * Generic bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Factory;

use Ding\Bean\Factory\Filter\PropertyFilter;

use Ding\Bean\BeanConstructorArgumentDefinition;

use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Factory\Exception\BeanFactoryException;

use Ding\Aspect\Proxy;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\Interceptor\DispatcherImpl;

/**
 * Generic bean factory.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
abstract class BeanFactory
{
    /**
     * Bean definitions already known.
     * @var BeanDefinition[]
     */
    private $_beanDefs;

    /**
     * Beans already instantiated.
     * @var object[]
     */
    private $_beans;

    /**
     * Registered filters to apply.
     * @var IFilter[]
     */
    private $_filters;

    /**
     * Directory to be used as a proxy cache.
     * @var string
     */
    private $_proxyCacheDir;
    
    /**
     * This will return the property value from a definition.
     * 
     * @param BeanPropertyDefinition $property Property definition.
     * 
     * @return mixed
     */
    private function _loadProperty(BeanPropertyDefinition $property)
    {
        $value = null;
        if ($property->isBean()) {
            $value = $this->getBean($property->getValue());
        } else if ($property->isArray()) {
            $value = array();
            foreach ($property->getValue() as $k => $v) {
                $value[$k] = $this->_loadProperty($v);
            }
        } else if ($property->isCode()) {
            $value = eval($property->getValue());
        } else {
            $value = $this->_applyFilters($property->getValue());
        }
        return $value;
    }
    
    /**
     * Applies all registered filters.
     * 
     * @param mixed $input Input to filter (typically a final value for a bean).
     * 
     * @return mixed
     */
    private function _applyFilters($input)
    {
        foreach ($this->_filters as $filter) {
            $input = $filter->apply($input);
        }
        return $input;
    }
    
    /**
     * This will assembly a bean (inject dependencies, loading other needed
     * beans in the way).
     * 
     * @param object         $bean Where to call 'setXXX' methods.
     * @param BeanDefinition $def  Bean definition, used to get needed 
     * properties.
     * 
     * @throws BeanFactoryException
     * @return void
     */
    private function _assemble($bean, BeanDefinition $def)
    {
        foreach ($def->getProperties() as $property) {
            $method = $this->_getSetterFor(
                $def->getClass(), $property->getName()
            );
            try
            {
                $method->invoke($bean, $this->_loadProperty($property));
            } catch (\ReflectionException $exception) {
                throw new BeanFactoryException('Error calling: ' . $value);
            }
        }
    }
    
    /**
     * Returns the method (a reflection method) used for di'ing stuff.
     * 
     * @param string $class Class name.
     * @param string $name  Property name. If name is 'name', then this will
     * return a method named 'setName'.
     * 
     * @return \ReflectionMethod
     */
    private function _getSetterFor($class, $name)
    {
        $rClass = new \ReflectionClass($class);
        return $rClass->getMethod('set' . ucfirst($name));
    }
    
    /**
     * This will return an argument value, from a definition.
     *
     * @param BeanConstructorArgumentDefinition $arg Constructor definition.
     * 
     * @return mixed
     */
    private function _loadArgument(BeanConstructorArgumentDefinition $arg)
    {
        $value = null;
        if ($arg->isBean()) {
            $value = $this->getBean($arg->getValue());
        } else if ($arg->isArray()) {
            $value = array();
            foreach ($arg->getValue() as $k => $v) {
                $value[$k] = $this->_loadArgument($v);
            }
        } else if ($arg->isCode()) {
            $value = eval($arg->getValue());
        } else {
            $value = $this->_applyFilters($arg->getValue());
        }
        return $value;
    }
    
    /**
     * This will create a new bean, injecting all properties and applying all
     * aspects.
     * 
     * @throws BeanFactoryException
     * @return object
     */
    private function _createBean(BeanDefinition $beanDefinition)
    {
        $beanClass = $beanDefinition->getClass();
        $args = array();
        foreach ($beanDefinition->getArguments() as $argument) {
            $args[] = $this->_loadArgument($argument);
        }
        
        if ($beanDefinition->hasAspects()) {
            $dispatcher = new DispatcherImpl();
            $beanClass = Proxy::create(
                $beanClass, $this->_proxyCacheDir, $dispatcher
            );
            foreach ($beanDefinition->getAspects() as $aspectDefinition) {
                $aspect = $this->getBean($aspectDefinition->getBeanName());
                if (
                    $aspectDefinition->getType() == AspectDefinition::ASPECT_METHOD
                ) {
                    $dispatcher->addMethodInterceptor(
                        $aspectDefinition->getPointcut(), $aspect
                    );
                } else {
                    $dispatcher->addExceptionInterceptor(
                        $aspectDefinition->getPointcut(), $aspect
                    );
                }
            }
        }
        /* @todo change this to a clone */
        if ($beanDefinition->getFactoryMethod() == false) {
            $constructor = new \ReflectionClass($beanClass);
            if (count($args) < 1) {
                $bean = $constructor->newInstanceArgs();
            } else {
                $bean = $constructor->newInstanceArgs($args);
            }
        } else {
            if ($beanDefinition->getFactoryBean() == false) {
                $beanFactoryMethod = $beanDefinition->getFactoryMethod();
                if (empty($args)) {
                    $bean = $beanClass::$beanFactoryMethod();
                } else {
                    /* @todo yikes! */
                    $bean = forward_static_call_array(
                        array($beanClass, $beanFactoryMethod),
                        $args
                    );
                }
            } else {
                $beanFactory = $this->getBean(
                    $beanDefinition->getFactoryBean()
                );
                $refObject = new \ReflectionObject($beanFactory);
                $method = $refObject->getMethod(
                    $beanDefinition->getFactoryMethod()
                );
                if (empty($args)) {
                    $bean = $method->invoke($beanFactory);
                } else {
                    $bean = $method->invokeArgs($beanFactory, $args);
                }
            }
        }
        try
        {
            $this->_assemble($bean, $beanDefinition);
            $initMethod = $beanDefinition->getInitMethod();
            if ($initMethod) {
                $bean->$initMethod();
            }
            $destroyMethod = $beanDefinition->getDestroyMethod();
            if ($destroyMethod) {
                register_shutdown_function(array($bean, $destroyMethod));
            }
        } catch(\ReflectionException $exception) {
            throw new BeanFactoryException('DI Error', 0, $exception);
        }
        return $bean;
    }
    
    /**
     * Override this one with your own implementation.
     *
     * @param string $beanName Bean to get definition for.
     * 
     * @return BeanDefinition
     */
    public abstract function getBeanDefinition($beanName);

    /**
     * Returns a bean.
     * 
     * @param string $beanName Bean name.
     * 
     * @throws BeanFactoryException
     * @return object
     */
    public function getBean($beanName)
    {
        $ret = false;
        if (!isset($this->_beanDefs[$beanName])) {
            $beanDefinition = $this->getBeanDefinition($beanName);
            if (!$beanDefinition) {
                throw new BeanFactoryException('Unknown bean: ' . $beanName);
            }
            $this->_beanDefs[$beanName] = $beanDefinition;
        } else {
            $beanDefinition = $this->getBeanDefinition($beanName);
        }
        switch ($beanDefinition->getScope())
        {
        case BeanDefinition::BEAN_PROTOTYPE:
            $ret = $this->_createBean($beanDefinition);
            break;
        case BeanDefinition::BEAN_SINGLETON:
            if (!isset($this->_beans[$beanName])) {
                $ret = $this->_createBean($beanDefinition);
                $this->_beans[$beanName] = $ret;
            } else {
                $ret = $this->_beans[$beanName];
            }
            break;
        default:
            throw new BeanFactoryException('Invalid bean scope');
        }
        return $ret;
    }
    
    /**
     * Constructor.
     *
     * @param array  $properties Container properties.
     * 
     * @return void
     */
    protected function __construct(array $properties = array()
    ) {
        $this->_beans = array();
        $this->_beanDefs = array();
        $this->_filters = array();
        $this->_proxyCacheDir = $properties['proxy.cache.dir'];
        @mkdir($this->_proxyCacheDir, 0750, true);
        $this->_filters[] = PropertyFilter::getInstance($properties);
    }
}
