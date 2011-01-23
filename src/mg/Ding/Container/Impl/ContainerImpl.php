<?php
/**
 * Container implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Container
 * @subpackage Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Container\Impl;

use Ding\Cache\Locator\CacheLocator;
use Ding\Container\IContainer;
use Ding\Container\Exception\ContainerException;
use Ding\Aspect\Proxy;
use Ding\Aspect\InterceptorDefinition;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\Interceptor\DispatcherImpl;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Lifecycle\BeanLifecycle;
use Ding\Bean\Lifecycle\IBeforeConfigListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\Lifecycle\IBeforeDestructListener;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Factory\Driver\BeanXmlDriver;
use Ding\Bean\Factory\Driver\MVCAnnotationDriver;
use Ding\Bean\Factory\Driver\DependsOnDriver;
use Ding\Bean\Factory\Driver\TimezoneDriver;
use Ding\Bean\Factory\Driver\ShutdownDriver;
use Ding\Bean\Factory\Driver\BeanAnnotationDriver;
use Ding\Bean\Factory\Driver\BeanCacheDefinitionDriver;
use Ding\Bean\Factory\Driver\BeanAspectDriver;
use Ding\Bean\Factory\Driver\FiltersDriver;
use Ding\Bean\Factory\Driver\ErrorHandlerDriver;
use Ding\Bean\Factory\Driver\SignalHandlerDriver;
use Ding\Bean\Factory\Driver\SetterInjectionDriver;
use Ding\Bean\Factory\Driver\AutowiredInjectionDriver;
use Ding\Bean\Factory\Driver\AnnotationAspectDriver;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;

/**
 * Container implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Container
 * @subpackage Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class ContainerImpl implements IContainer
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Default options.
     * @var array
     */
    private static $_options = array(
        'bdef' => array(),
        'properties' => array()
    );

    /**
     * Registered shutdown methods for beans (destroy-methods).
     * @var array
     */
    private $_shutdowners;

    /**
     * Beans already instantiated.
     * @var object[]
     */
    private $_beans;

    /**
     * Holds our beans cache.
     * @var ICache
     */
    private $_beanCache;

    /**
     * Beans already instantiated.
     * @var BeanDefinition[]
     */
    private $_beanDefs;

    /**
     * Holds our bean definitions cache.
     * @var ICache
     */
    private $_beanDefCache;

    /**
     * Lifecycle handlers for beans.
     * @var ILifecycleListener
     */
    private $_lifecyclers;

    /**
     * Container instance.
     * @var ContainerImpl
     */
    private static $_containerInstance = false;

    /**
     * Returns a bean definition.
     *
     * @param string $name Bean name.
     *
     * @return BeanDefinition
     * @throws BeanFactoryException
     */
    public function getBeanDefinition($name)
    {
        $beanName = $name . '.beandef';
        if (isset($this->_beanDefs[$name])) {
            if ($this->_logger->isDebugEnabled()) {
                $this->_logger->debug('Serving already known: ' . $beanName);
            }
            return $this->_beanDefs[$name];
        }

        $result = false;
        $beanDefinition = $this->_beanDefCache->fetch($beanName, $result);
        if ($result !== false) {
            $this->_beanDefs[$name] = $beanDefinition;
            if ($this->_logger->isDebugEnabled()) {
                $this->_logger->debug('Serving cached: ' . $beanName);
            }
            return $beanDefinition;
        }
        $beanDefinition = null;
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('Running BeforeDefinition: ' . $beanName);
        }
        foreach ($this->_lifecyclers[BeanLifecycle::BeforeDefinition] as $lifecycleListener) {
            $beanDefinition = $lifecycleListener->beforeDefinition(
                $this, $name, $beanDefinition
            );
        }
        if ($beanDefinition === null) {
            throw new BeanFactoryException('Unknown bean: ' . $name);
        }
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('Running AfterDefinition: ' . $beanName);
        }
        foreach ($this->_lifecyclers[BeanLifecycle::AfterDefinition] as $lifecycleListener) {
            $beanDefinition = $lifecycleListener->afterDefinition($this, $beanDefinition);
        }
        $this->setBeanDefinition($name, $beanDefinition);
        return $beanDefinition;
    }

    /**
     * Sets a bean definition (adds or overwrites).
     *
     * @param string         $name       Bean name.
     * @param BeanDefinition $definition New bean definition.
     *
     * @return void
     */
    public function setBeanDefinition($name, BeanDefinition $definition)
    {
        $beanName = $name . '.beandef';
        $this->_beanDefs[$name] = $definition;
        $this->_beanDefCache->store($beanName, $definition);
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('New: ' . $beanName);
        }
    }

    /**
     * Sets a bean (adds or overwrites).
     *
     * @param string $name Bean name.
     * @param object $bean New object.
     *
     * @return void
     */
    public function setBean($name, $bean)
    {
        $beanName = $name . '.bean';
        $this->_beans[$name] = $bean;

        /**
         * @todo This is not suppose to exist. We need to refactor the proxy so it
		 * can be correctly serialized. This check is used internally by the
		 * container to know that this bean cant be cached (although it can cache
		 * its definition).
		 */
        if (!isset($bean::$iAmADingProxy)) {
            $this->_beanCache->store($beanName, $bean);
        }
        if ($this->_logger->isDebugEnabled()) {
            $this->_logger->debug('New: ' . $beanName);
        }
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
        foreach ($this->_lifecyclers[BeanLifecycle::BeforeAssemble] as $lifecycleListener) {
            $bean = $lifecycleListener->beforeAssemble($this, $bean, $def);
        }
        foreach ($this->_lifecyclers[BeanLifecycle::AfterAssemble] as $lifecycleListener) {
            $bean = $lifecycleListener->afterAssemble($this, $bean, $def);
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
            $value = $arg->getValue();
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
        foreach ($this->_lifecyclers[BeanLifecycle::BeforeCreate] as $lifecycleListener) {
            $lifecycleListener->beforeCreate($this, $beanDefinition);
        }
        $beanClass = $beanDefinition->getClass();
        $args = array();
        foreach ($beanDefinition->getArguments() as $argument) {
            $args[] = $this->_loadArgument($argument);
        }
        $dispatcher = new DispatcherImpl();
        $methods = array();
        if ($beanDefinition->hasAspects()) {
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
        $factoryMethod = $beanDefinition->getFactoryMethod();
        if ($factoryMethod == false || empty($factoryMethod)) {
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
        foreach ($this->_lifecyclers[BeanLifecycle::AfterCreate] as $lifecycleListener) {
            $bean = $lifecycleListener->afterCreate($this, $bean, $beanDefinition);
        }
        try
        {
            $this->_assemble($bean, $beanDefinition);
            if (!empty($beanClass)) {
                $annotations = ReflectionFactory::getClassAnnotations($beanDefinition->getClass());
                if (isset($annotations['class']['InitMethod'])) {
                    $arguments = $annotations['class']['InitMethod']->getArguments();
                    if (isset($arguments['method'])) {
                        $beanDefinition->setInitMethod($arguments['method']);
                    }
                }
                if (isset($annotations['class']['DestroyMethod'])) {
                    $arguments = $annotations['class']['DestroyMethod']->getArguments();
                    if (isset($arguments['method'])) {
                        $beanDefinition->setDestroyMethod($arguments['method']);
                    }
                }
            }
            $initMethod = $beanDefinition->getInitMethod();
            if ($initMethod) {
                $bean->$initMethod();
            }
            $destroyMethod = $beanDefinition->getDestroyMethod();
            if ($destroyMethod) {
                $this->registerShutdownMethod($bean, $destroyMethod);
            }
        } catch(\ReflectionException $exception) {
            throw new BeanFactoryException('DI Error', 0, $exception);
        }
        return $bean;
    }

    /**
     * Returns a bean.
     *
     * @param string $name Bean name.
     *
     * @throws BeanFactoryException
     * @return object
     */
    public function getBean($name)
    {
        $ret = false;
        $beanDefinition = $this->getBeanDefinition($name);
        $beanName = $name . '.bean';
        switch ($beanDefinition->getScope())
        {
        case BeanDefinition::BEAN_PROTOTYPE:
            $ret = $this->_createBean($beanDefinition);
            break;
        case BeanDefinition::BEAN_SINGLETON:
            if (!isset($this->_beans[$name])) {
                $result = false;
                $ret = $this->_beanCache->fetch($beanName, $result);
                if ($result === false) {
                    $ret = $this->_createBean($beanDefinition);
                } else {
                    if ($this->_logger->isDebugEnabled()) {
                        $this->_logger->debug('Serving cached: ' . $beanName);
                    }
                }
                $this->setBean($name, $ret);
            } else {
                if ($this->_logger->isDebugEnabled()) {
                    $this->_logger->debug('Serving already known: ' . $beanName);
                }
                $ret = $this->_beans[$name];
            }
            break;
        default:
            throw new BeanFactoryException('Invalid bean scope');
        }
        return $ret;
    }

    /**
     * This will return a container
     *
     * @param array $properties Container properties.
     *
     * @return ContainerImpl
     */
    public static function getInstance(array $properties = array())
    {
        if (self::$_containerInstance === false) {
            // Init cache subsystems.
            if (isset($properties['ding']['cache'])) {
                CacheLocator::configure($properties['ding']['cache']);
            }
            if (isset($properties['ding']['log4php.properties'])) {
                \Logger::configure($properties['ding']['log4php.properties']);
            }
            $ret = new ContainerImpl($properties['ding']['factory']);
            self::$_containerInstance = $ret;
        } else {
            $ret = self::$_containerInstance;
        }
        return $ret;
    }

    /**
     * Register a shutdown (destroy-method) method for a bean.
     *
     * @param object $bean   Bean to call.
     * @param string $method Method to call.
     *
     * @see Ding\Container.IContainer::registerShutdownMethod()
     *
     * @return void
     */
    public function registerShutdownMethod($bean, $method)
    {
        $this->_shutdowners[] = array($bean, $method);
    }

    /**
     * Destructor, will call all beans destroy-methods.
     *
     * @return void
     */
    public function __destruct()
    {
        foreach ($this->_shutdowners as $shutdownCall) {
            $bean = $shutdownCall[0];
            $method = $shutdownCall[1];
            $bean->$method();
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addBeforeConfigListener()
     */
    public function addBeforeConfigListener(IBeforeConfigListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeConfig][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addAfterConfigListener()
     */
    public function addAfterConfigListener(IAfterConfigListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterConfig][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addBeforeDefinitionListener()
     */
    public function addBeforeDefinitionListener(IBeforeDefinitionListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeDefinition][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addAfterDefinitionListener()
     */
    public function addAfterDefinitionListener(IAfterDefinitionListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterDefinition][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addBeforeCreateListener()
     */
    public function addBeforeCreateListener(IBeforeCreateListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeCreate][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addAfterCreateListener()
     */
    public function addAfterCreateListener(IAfterCreateListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterCreate][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addBeforeAssembleListener()
     */
    public function addBeforeAssembleListener(IBeforeAssembleListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeAssemble][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addAfterAssembleListener()
     */
    public function addAfterAssembleListener(IAfterAssembleListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterAssemble][] = $listener;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::addBeforeDestructListener()
     */
    public function addBeforeDestructListener(IBeforeDestructListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeDestruction][] = $listener;
    }

    /**
     * Constructor.
     *
     * @param array $options options.
     *
     * @return void
     */
    protected function __construct(array $options)
    {
        $this->_logger = \Logger::getLogger('Ding.Container');
        $soullessArray = array();
        self::$_options = array_replace_recursive(self::$_options, $options);

        $this->_beanDefs = $soullessArray;
        $this->_beanDefCache = CacheLocator::getDefinitionsCacheInstance();
        $this->_beans = $soullessArray;
        $this->_beanCache = CacheLocator::getBeansCacheInstance();
        $this->_shutdowners = $soullessArray;

        $this->_lifecyclers = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeConfig] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterConfig] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeDefinition] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterDefinition] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeCreate] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterCreate] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeAssemble] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterAssemble] = $soullessArray;

        if (isset(self::$_options['bdef']['annotation'])) {
            $this->addBeforeConfigListener(
                BeanAnnotationDriver::getInstance(self::$_options['bdef']['annotation'])
            );
            $this->addAfterConfigListener(
                BeanAnnotationDriver::getInstance(self::$_options['bdef']['annotation'])
            );
            $this->addBeforeDefinitionListener(
                BeanAnnotationDriver::getInstance(self::$_options['bdef']['annotation'])
            );
            //$this->addAfterCreateListener(AutowiredInjectionDriver::getInstance($soullessArray));
        }
        $this->addAfterConfigListener(ErrorHandlerDriver::getInstance($soullessArray));
        $this->addAfterConfigListener(SignalHandlerDriver::getInstance($soullessArray));
        $this->addAfterConfigListener(ShutdownDriver::getInstance($soullessArray));
        $this->addAfterConfigListener(TimezoneDriver::getInstance($soullessArray));
        $this->addAfterDefinitionListener(FiltersDriver::getInstance(self::$_options['properties']));
        $this->addAfterDefinitionListener(DependsOnDriver::getInstance($soullessArray));

        if (isset(self::$_options['bdef']['xml'])) {
            $this->addBeforeDefinitionListener(BeanXmlDriver::getInstance(self::$_options['bdef']['xml']));
        }
        $this->addAfterConfigListener(MVCAnnotationDriver::getInstance($soullessArray));
        $this->addBeforeAssembleListener(SetterInjectionDriver::getInstance($soullessArray));

        foreach ($this->_lifecyclers[BeanLifecycle::BeforeConfig] as $lifecycleListener) {
            $lifecycleListener->beforeConfig($this);
        }
        foreach ($this->_lifecyclers[BeanLifecycle::AfterConfig] as $lifecycleListener) {
            $lifecycleListener->afterConfig($this);
        }
    }
}
