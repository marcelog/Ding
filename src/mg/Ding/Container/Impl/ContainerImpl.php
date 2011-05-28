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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
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
namespace Ding\Container\Impl;

use Ding\Bean\Factory\Driver\PropertiesDriver;
use Ding\Bean\Factory\Driver\ResourcesDriver;
use Ding\Resource\Impl\IncludePathResource;
use Ding\Resource\Impl\FilesystemResource;
use Ding\Resource\Impl\URLResource;
use Ding\Cache\Locator\CacheLocator;
use Ding\Container\IContainer;
use Ding\Aspect\Proxy;
use Ding\Aspect\AspectManager;
use Ding\Aspect\InterceptorDefinition;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\Interceptor\DispatcherImpl;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Lifecycle\BeanLifecycle;
use Ding\Bean\Lifecycle\BeanLifecycleManager;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Factory\Driver\BeanXmlDriver;
use Ding\Bean\Factory\Driver\BeanYamlDriver;
use Ding\Bean\Factory\Driver\MVCAnnotationDriver;
use Ding\Bean\Factory\Driver\DependsOnDriver;
use Ding\Bean\Factory\Driver\MessageSourceDriver;
use Ding\Bean\Factory\Driver\MethodInjectionDriver;
use Ding\Bean\Factory\Driver\ShutdownDriver;
use Ding\Bean\Factory\Driver\BeanAnnotationDriver;
use Ding\Bean\Factory\Driver\BeanCacheDefinitionDriver;
use Ding\Bean\Factory\Driver\BeanAspectDriver;
use Ding\Bean\Factory\Driver\ErrorHandlerDriver;
use Ding\Bean\Factory\Driver\SignalHandlerDriver;
use Ding\Bean\Factory\Driver\SetterInjectionDriver;
use Ding\Bean\Factory\Driver\AnnotationAspectDriver;
use Ding\Bean\Factory\Driver\AnnotationRequiredDriver;
use Ding\Bean\Factory\Driver\AnnotationResourceDriver;
use Ding\Bean\Factory\Driver\AnnotationInitDestroyMethodDriver;
use Ding\Bean\Factory\Driver\ContainerAwareDriver;
use Ding\Bean\Factory\Driver\LoggerAwareDriver;
use Ding\Bean\Factory\Driver\ResourceLoaderAwareDriver;
use Ding\Bean\Factory\Driver\BeanNameAwareDriver;
use Ding\Bean\Factory\Driver\AspectManagerAwareDriver;
use Ding\Bean\Factory\Driver\LifecycleDriver;
use Ding\Bean\Factory\Exception\BeanFactoryException;
use Ding\Bean\BeanConstructorArgumentDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\MessageSource\IMessageSource;

/**
 * Container implementation.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Container
 * @subpackage Impl
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class ContainerImpl implements IContainer
{
    /**
     * log4php logger or our own.
     * @var Logger
     */
    private $_logger;

    /**
     * Cache for isDebugEnabled()
     * @var boolean
     */
    private $_logDebugEnabled;

    /**
     * Dispatcher to be cloned for proxy.
     * @var DispatcherImpl
     */
    private $_dispatcherTemplate = false;

    /**
     * MessageSource implementation.
     * @var IMessageSource
     */
    private $_messageSource = false;

    /**
     * Default options.
     * @var array
     */
    private static $_options = array(
        'bdef' => array(),
        'properties' => array(),
        'drivers' => array()
    );

    /**
     * Registered shutdown methods for beans (destroy-methods).
     * @var array
     */
    private $_shutdowners = array();

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
     * Container instance.
     * @var ContainerImpl
     */
    private static $_containerInstance = false;

    /**
     * The aspect manager.
     * @var AspectManager
     */
    private $_aspectManager = false;

    /**
     * The lifecycle manager.
     * @var BeanLifecycleManager
     */
    private $_lifecycleManager = false;

    /**
     * Resources multiton.
     * @var IResource[]
     */
    private $_resources = false;

    /**
     * Prevent serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_aspectManager', '_lifecycleManager');
    }

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
            if ($this->_logDebugEnabled) {
                $this->_logger->debug('Serving already known: ' . $beanName);
            }
            return $this->_beanDefs[$name];
        }

        $result = false;
        $beanDefinition = $this->_beanDefCache->fetch($beanName, $result);
        if ($result !== false) {
            $this->_beanDefs[$name] = $beanDefinition;
            if ($this->_logDebugEnabled) {
                $this->_logger->debug('Serving cached: ' . $beanName);
            }
            return $beanDefinition;
        }
        $beanDefinition = null;
        if ($this->_logDebugEnabled) {
            $this->_logger->debug('Running BeforeDefinition: ' . $beanName);
        }
        $beanDefinition = $this->_lifecycleManager->beforeDefinition($this, $name, $beanDefinition);

        if ($beanDefinition === null) {
            throw new BeanFactoryException('Unknown bean: ' . $name);
        }
        if ($this->_logDebugEnabled) {
            $this->_logger->debug('Running AfterDefinition: ' . $beanName);
        }
        $beanDefinition = $this->_lifecycleManager->afterDefinition($this, $beanDefinition);
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
        if ($this->_logDebugEnabled) {
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
        //if (!isset($bean::$iAmADingProxy)) {
        //    $this->_beanCache->store($beanName, $bean);
        //}
        if ($this->_logDebugEnabled) {
            $this->_logger->debug('New: ' . $beanName);
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

    private function _applyAspect(
        AspectDefinition $aspectDefinition, DispatcherImpl $dispatcher, \ReflectionClass $rClass, array &$methods
    ) {
        $aspect = $this->getBean($aspectDefinition->getBeanName());
        foreach ($aspectDefinition->getPointcuts() as $pointcutName) {
            $pointcut = $this->_aspectManager->getPointcut($pointcutName);
            if ($pointcut === false) {
                throw new BeanFactoryException('Could not find pointcut: ' . $pointcutName);
            }
            $expression = $pointcut->getExpression();
            foreach  ($rClass->getMethods() as $method) {
                $methodName = $method->getName();
                if (preg_match('/' . $expression . '/', $methodName) === 0) {
                    continue;
                }
                $methods[$methodName] = '';
                if (
                    $aspectDefinition->getType() == AspectDefinition::ASPECT_METHOD
                ) {
                    $dispatcher->addMethodInterceptor($methodName, $aspect, $pointcut->getMethod());
                } else {
                    $dispatcher->addExceptionInterceptor($methodName, $aspect, $pointcut->getMethod());
                }
            }
        }
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
        $this->_lifecycleManager->beforeCreate($this, $beanDefinition);

        $beanClass = $beanDefinition->getClass();
        $args = array();
        foreach ($beanDefinition->getArguments() as $argument) {
            $args[] = $this->_loadArgument($argument);
        }
        $rClass = false;
        if (!empty($beanClass)) {
            $rClass = ReflectionFactory::getClass($beanClass);
        }
        $dispatcher = clone $this->_dispatcherTemplate;
        $methods = array();
        if ($beanDefinition->hasAspects()) {
            /**
             * @todo the operation of applying an aspect is really expensive!
             */
            foreach ($beanDefinition->getAspects() as $aspect) {
                $this->_applyAspect($aspect, $dispatcher, $rClass, $methods);
            }
        }
        if ($rClass !== false) {
            foreach ($this->_aspectManager->getAspects() as $aspect) {
                $expression = $aspect->getExpression();
                if (preg_match('/' . $expression . '/', $beanClass) === 0) {
                    continue;
                }
                $this->_applyAspect($aspect, $dispatcher, $rClass, $methods);
            }
        }
        if (!empty($methods)) {
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
        $this->_lifecycleManager->afterCreate($this, $bean, $beanDefinition);
        $this->_lifecycleManager->beforeAssemble($this, $bean, $beanDefinition);

        $initMethod = $beanDefinition->getInitMethod();
        if ($initMethod) {
            $bean->$initMethod();
        }
        $destroyMethod = $beanDefinition->getDestroyMethod();
        if ($destroyMethod) {
            $this->registerShutdownMethod($bean, $destroyMethod);
        }
        $this->_lifecycleManager->afterAssemble($this, $bean, $beanDefinition);
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
        if ($beanDefinition->isPrototype()) {
            $ret = $this->_createBean($beanDefinition);
        } else if ($beanDefinition->isSingleton()) {
            if (!isset($this->_beans[$name])) {
                $result = false;
                $ret = $this->_beanCache->fetch($beanName, $result);
                if ($result === false) {
                    $ret = $this->_createBean($beanDefinition);
                }
                $this->setBean($name, $ret);
            } else {
                if ($this->_logDebugEnabled) {
                    $this->_logger->debug('Serving already known: ' . $beanName);
                }
                $ret = $this->_beans[$name];
            }
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
            // Init ReflectionFactory
            ReflectionFactory::configure(isset($properties['ding']['factory']['bdef']['annotation']));

            // Init cache subsystems.
            if (isset($properties['ding']['cache'])) {
                CacheLocator::configure($properties['ding']['cache']);
            }
            \Ding\Autoloader\Autoloader::setCache(CacheLocator::getAutoloaderCacheInstance());
            if (isset($properties['ding']['log4php.properties'])) {
                \Logger::configure($properties['ding']['log4php.properties']);
            }
            self::$_containerInstance = new ContainerImpl($properties['ding']['factory']);
        }
        return self::$_containerInstance;
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

    public function setMessageSource(IMessageSource $messageSource)
    {
        $this->_messageSource = $messageSource;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\MessageSource.IMessageSource::getMessage()
     */
    public function getMessage($bundle, $message, array $arguments, $locale)
    {
        return
            $this->_messageSource !== false
            ? $this->_messageSource->getMessage($bundle, $message, $arguments, $locale)
            : NULL;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResourceLoader::getResource()
     */
    public function getResource($location, $context = false)
    {
        // Missing scheme?
        $scheme = strpos($location, '://');
        if ($scheme === false) {
            $location = FilesystemResource::SCHEME . $location;
        }
        // Already served?
        if (isset($this->_resources[$location])) {
            return $this->_resources[$location];
        }
        // See what kind of resource to return.
        if (strpos($location, FilesystemResource::SCHEME) === 0) {
            $resource = new FilesystemResource($location, $context);
        } else if (strpos($location, IncludePathResource::SCHEME) === 0) {
            $resource = new IncludePathResource($location, $context);
        } else {
            $resource = new URLResource($location, $context);
        }
        $this->_resources[$location] = $resource;
        return $resource;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::getLogger()
     */
    public function getLogger($class)
    {
        return \Logger::getLogger(str_replace('\\', '.', $class));
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
        $this->_lifecycleManager = BeanLifecycleManager::getInstance();
        $this->_dispatcherTemplate = new DispatcherImpl;
        $this->_logDebugEnabled = $this->_logger->isDebugEnabled();
        $soullessArray = array();
        self::$_options = array_replace_recursive(self::$_options, $options);

        $this->_aspectManager = AspectManager::getInstance();
        $this->_beanDefs = $soullessArray;
        $this->_beanDefCache = CacheLocator::getDefinitionsCacheInstance();
        $this->_beans = $soullessArray;
        $this->_beanCache = CacheLocator::getBeansCacheInstance();
        $this->_shutdowners = $soullessArray;
        $this->_resources = $soullessArray;

        $this->_lifecycleManager->addAfterCreateListener(ContainerAwareDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterCreateListener(LoggerAwareDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterCreateListener(ResourceLoaderAwareDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterDefinitionListener(BeanNameAwareDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterDefinitionListener(AspectManagerAwareDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterAssembleListener(LifecycleDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addBeforeCreateListener(ResourcesDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addAfterConfigListener(PropertiesDriver::getInstance(self::$_options['properties']));

        if (isset(self::$_options['bdef']['annotation'])) {
            $anDriver = BeanAnnotationDriver::getInstance(self::$_options['bdef']['annotation']);
            $this->_lifecycleManager->addBeforeConfigListener($anDriver);
            $this->_lifecycleManager->addAfterConfigListener($anDriver);
            $this->_lifecycleManager->addBeforeDefinitionListener($anDriver);
            $this->_lifecycleManager->addAfterConfigListener(MVCAnnotationDriver::getInstance($soullessArray));
            $this->_lifecycleManager->addAfterDefinitionListener(AnnotationResourceDriver::getInstance($soullessArray));
            $this->_lifecycleManager->addAfterCreateListener(AnnotationResourceDriver::getInstance($soullessArray));
            $this->_lifecycleManager->addAfterConfigListener(AnnotationAspectDriver::getInstance($soullessArray));
            $this->_lifecycleManager->addAfterDefinitionListener(AnnotationRequiredDriver::getInstance($soullessArray));
            $this->_lifecycleManager->addAfterDefinitionListener(AnnotationInitDestroyMethodDriver::getInstance($soullessArray));
        }

        if (isset(self::$_options['drivers']['errorhandler'])) {
            $this->_lifecycleManager->addAfterConfigListener(ErrorHandlerDriver::getInstance($soullessArray));
        }

        if (isset(self::$_options['drivers']['signalhandler'])) {
            $this->_lifecycleManager->addAfterConfigListener(SignalHandlerDriver::getInstance($soullessArray));
        }

        if (isset(self::$_options['drivers']['shutdown'])) {
            $this->_lifecycleManager->addAfterConfigListener(ShutdownDriver::getInstance($soullessArray));
        }

        $this->_lifecycleManager->addBeforeCreateListener(DependsOnDriver::getInstance($soullessArray));

        if (isset(self::$_options['bdef']['xml'])) {
            $xmlDriver = BeanXmlDriver::getInstance(self::$_options['bdef']['xml']);
            $this->_lifecycleManager->addBeforeDefinitionListener($xmlDriver);
            $this->_aspectManager->registerAspectProvider($xmlDriver);
            $this->_aspectManager->registerPointcutProvider($xmlDriver);
        }
        if (isset(self::$_options['bdef']['yaml'])) {
            $yamlDriver = BeanYamlDriver::getInstance(self::$_options['bdef']['yaml']);
            $this->_lifecycleManager->addBeforeDefinitionListener($yamlDriver);
            $this->_aspectManager->registerAspectProvider($yamlDriver);
            $this->_aspectManager->registerPointcutProvider($yamlDriver);
        }

        $this->_lifecycleManager->addBeforeAssembleListener(SetterInjectionDriver::getInstance($soullessArray));
        $this->_lifecycleManager->addBeforeDefinitionListener(MethodInjectionDriver::getInstance($soullessArray));
        $messageSourceDriver = MessageSourceDriver::getInstance($soullessArray);
        $this->_lifecycleManager->addAfterConfigListener($messageSourceDriver);
        $this->_lifecycleManager->addAfterCreateListener($messageSourceDriver);
        $this->_lifecycleManager->beforeConfig($this);
        $this->_lifecycleManager->afterConfig($this);
    }
}
