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


use Ding\Cache\CacheLocator;
use Ding\Reflection\ReflectionFactory;
use Ding\Container\IContainer;

use Ding\Bean\Factory\Driver\BeanXmlDriver;
use Ding\Bean\Factory\Driver\BeanAnnotationDriver;
use Ding\Bean\Factory\Driver\BeanCacheDefinitionDriver;
use Ding\Bean\Factory\Driver\BeanAspectDriver;
use Ding\Bean\Factory\Filter\PropertyFilter;
use Ding\Bean\Factory\Exception\BeanFactoryException;

use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;

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
class BeanFactory
{
    /**
     * Default options.
     * @var array
     */
    private static $_options = array(
        'xml' => array('filename' => 'beans.xml'),
    	'annotation' => array(),
        'properties' => array()
    );
    
    /**
     * Holds our instance.
     * @var BeanFactory
     */
    private static $_instance = false;
    
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
     * Our calling container. 
     * @var IContainer
     */
    private $_container;
    
    /**
     * Cache property setters names.
     * @var array[]
     */
    private $_propertiesNameCache;

    /**
     * Lifecycle handlers for beans. 
     * @var ILifecycleListener
     */
    private $_lifecyclers;
    
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
            $propertyName = $property->getName();
            if (isset($this->_propertiesNameCache[$propertyName])) {
                $methodName = $this->_propertiesNameCache[$propertyName];
            } else {
                $methodName = 'set' . ucfirst($propertyName);
                $this->_propertiesNameCache[$propertyName] = $methodName;
            }
            try
            {
                $bean->$methodName($this->_loadProperty($property));
            } catch (\ReflectionException $exception) {
                throw new BeanFactoryException('Error calling: ' . $methodName);
            }
        }
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
            $methods = array();
            foreach ($beanDefinition->getAspects() as $aspectDefinition) {
                $aspect = $this->getBean($aspectDefinition->getBeanName());
                $method = $aspectDefinition->getPointcut();
                $methods[$method] = '';
                if (
                    $aspectDefinition->getType() == AspectDefinition::ASPECT_METHOD
                ) {
                    $dispatcher->addMethodInterceptor($method, $aspect);
                } else {
                    $dispatcher->addExceptionInterceptor($method, $aspect);
                }
            }
            $beanClass = Proxy::create($beanClass, $methods, $dispatcher);
        }
        /* @todo change this to a clone */
        if ($beanDefinition->getFactoryMethod() == false) {
            $constructor = ReflectionFactory::getClass($beanClass);
            if (empty($args)) {
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
            foreach ($this->_lifecyclers as $lifecycleListener) {
                $bean = $lifecycleListener->beforeAssemble(
                    $bean, $beanDefinition
                );
            }
            $this->_assemble($bean, $beanDefinition);
            foreach ($this->_lifecyclers as $lifecycleListener) {
                $bean = $lifecycleListener->afterAssemble(
                    $bean, $beanDefinition
                );
            }
            $initMethod = $beanDefinition->getInitMethod();
            if ($initMethod) {
                $bean->$initMethod();
            }
            $destroyMethod = $beanDefinition->getDestroyMethod();
            if ($destroyMethod) {
                $this->_container->registerShutdownMethod($bean, $destroyMethod);
            }
        } catch(\ReflectionException $exception) {
            throw new BeanFactoryException('DI Error', 0, $exception);
        }
        return $bean;
    }
    
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
        $beanDefinition = null;
        foreach ($this->_lifecyclers as $lifecycleListener) {
            $beanDefinition = $lifecycleListener->beforeDefinition(
                $beanName, $beanDefinition
            );
        }
        if ($beanDefinition === null) {
            throw new BeanFactoryException('Unknown bean: ' . $beanName);
        }
        foreach ($this->_lifecyclers as $lifecycleListener) {
            $beanDefinition = $lifecycleListener->afterDefinition(
                $beanName, $beanDefinition
            );
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
     * Sets current calling container.
     * @return void
     */
    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }
    
    /**
     * Returns current calling container.
     * @return IContainer
     */
    public function getContainer()
    {
        return $this->_container;
    }
    
    /**
     * The container will call this one, in order to setup options. If any
     * option is missing, we use our default options as fallback.
     * 
     * @param array $options options.
     * 
     * @see BeanFactory::$_options
     * @return void
     */
    public static function configure(array $options)
    {
        self::$_options = array_replace_recursive(self::$_options, $options);
    }

    public static function getInstance()
    {
        if (self::$_instance === false) {
            $ret = new BeanFactory();
        } else {
            $ret = self::$_instance;
        }
        return $ret;
    }
            
    /**
     * Constructor.
     *
     * @param array $properties Container properties.
     * 
     * @return void
     */
    protected function __construct()
    {
        $this->_beans = array();
        $this->_filters = array();
        $this->_propertiesNameCache = array();
        $this->_filters[] = PropertyFilter::getInstance(
            self::$_options['properties']
        );
        $this->_lifecyclers = array();
        $this->_lifecyclers[] = BeanCacheDefinitionDriver::getInstance(array());
        if (isset(self::$_options['xml'])) {
            $this->_lifecyclers[]
                = BeanXmlDriver::getInstance(self::$_options['xml']);
            ;
        }
        if (isset(self::$_options['annotation'])) {
            $this->_lifecyclers[]
                = BeanAnnotationDriver::getInstance(
                    self::$_options['annotation']
                );
            ;
        }
    }
}
