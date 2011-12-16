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

use Ding\Helpers\ErrorHandler\ErrorInfo;

use Ding\Bean\IBeanDefinitionProvider;
use Ding\Cache\Impl\DummyCacheImpl;
use Ding\Bean\Provider\Core;
use Ding\Resource\Impl\IncludePathResource;
use Ding\Resource\Impl\FilesystemResource;
use Ding\Resource\Impl\URLResource;
use Ding\Cache\Locator\CacheLocator;
use Ding\Container\IContainer;
use Ding\Aspect\AspectManager;
use Ding\Aspect\InterceptorDefinition;
use Ding\Aspect\AspectDefinition;
use Ding\Aspect\Interceptor\IDispatcher;
use Ding\Aspect\Interceptor\DispatcherImpl;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Lifecycle\BeanLifecycle;
use Ding\Bean\Lifecycle\BeanLifecycleManager;
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
     * Signals to handle.
     * @var array
     */
    private $_signals = array(
        SIGQUIT, SIGHUP, SIGINT, SIGCHLD, SIGTERM, SIGUSR1, SIGUSR2
    );
    /**
     * Logger.
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
    private $_dispatcherTemplate = null;

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
     * Beans aliases.
     * @var string[]
     */
    private $_beanAliases;

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
    private $_aspectManager = null;

    /**
     * The lifecycle manager.
     * @var BeanLifecycleManager
     */
    private $_lifecycleManager = null;

    /**
     * Resources multiton.
     * @var IResource[]
     */
    private $_resources = false;

    /**
     * The event listeners
     * @var string[]
     */
    private $_eventListeners = false;

    /**
     * Bean Definition providers.
     * @var IBeanDefinitionProvider[]
     */
    private $_beanDefinitionProviders = array();

    /**
     * The last error message is saved, just to avoid logging repeated messages.
     * @var string
     */
    private $_lastErrorMessage;

    /**
     * A ReflectionFactory implementation
     * @var IReflectionFactory
     */
    private $_reflectionFactory;

    /**
     * A Proxy factory implementation.
     * @var Proxy
     */
    private $_proxyFactory;

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
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeanDefinitionByClass($class)
    {
        foreach ($this->_beanDefinitionProviders as $provider) {
            $beanDefinition = $provider->getBeanDefinitionByClass($class);
        }
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
        if (isset($this->_beanAliases[$name])) {
            $name = $this->_beanAliases[$name];
        }
        if (isset($this->_beanDefs[$name])) {
            return $this->_beanDefs[$name];
        }

        $beanDefinition = null;
        if ($this->_beanDefCache !== null) {
            $beanDefinition = $this->_beanDefCache->fetch($beanName, $result);
        }
        if ($beanDefinition) {
            $this->_beanDefs[$name] = $beanDefinition;
            return $beanDefinition;
        }
        foreach ($this->_beanDefinitionProviders as $provider) {
            $beanDefinition = $provider->getBeanDefinition($name);
            if ($beanDefinition) {
                break;
            }
        }
        if (!$beanDefinition) {
            throw new BeanFactoryException('Unknown bean: ' . $name);
        }
        $beanDefinition = $this->_lifecycleManager->afterDefinition($beanDefinition);
        $this->_beanDefs[$beanName] = $beanDefinition;
        $this->_beanDefCache->store($beanName, $beanDefinition);
        foreach ($beanDefinition->getAliases() as $alias) {
            $this->_beanAliases[$alias] = $name;
        }
        return $beanDefinition;
    }

    /**
     * Takes care of transforming a scalar value for a property or constructor
     * argument, into a an actual value (i.e: if its a resource://, loading it
     * first).
     *
     * @param mixed $value The value
     *
     * @return mixed
     */
    private function _loadValue($value)
    {
        if (is_string($value)) {
            if (strpos($value, 'resource://') === 0) {
                $value = substr($value, 11);
                return $this->getResource($value);
            }
        }
        return $value;
    }

    /**
     * This will resolve a property (or constructor arg) definition to a final
     * value, being a bean reference, array of other properties (or
     * constructor args), etc.
	 *
     * @param BeanPropertyDefinition|BeanConstructorArgumentDefinition $what
     *
     * @return void
     */
    private function _loadArgOrProperty($what)
    {
        $value = null;
        if ($what->isBean()) {
            $value = $this->getBean($what->getValue());
        } else if ($what->isArray()) {
            $value = array();
            foreach ($what->getValue() as $k => $v) {
                $value[$k] = $this->_loadArgOrProperty($v);
            }
        } else if ($what->isCode()) {
            $value = eval($what->getValue());
        } else {
            $value = $this->_loadValue($what->getValue());
        }
        return $value;
    }

    /**
     * Will inject into the given dispatcher the necessary information to
     * aspects will be run correctly.
     *
     * @param AspectDefinition $aspectDefinition
     * @param DispatcherImpl $dispatcher
     * @param \ReflectionClass $rClass
     * @param array $methods
     *
     * @throws BeanFactoryException
     * @return void
     */
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
        foreach ($beanDefinition->getDependsOn() as $depBean) {
            $this->getBean(trim($depBean));
        }
        $this->_lifecycleManager->beforeCreate($beanDefinition);

        $beanClass = $beanDefinition->getClass();
        $args = array();
        foreach ($beanDefinition->getArguments() as $argument) {
            $args[] = $this->_loadArgOrProperty($argument);
        }
        $rClass = false;
        if (!empty($beanClass)) {
            $rClass = $this->_reflectionFactory->getClass($beanClass);
        }
        $methods = array();
        $dispatcher = $this->_dispatcherTemplate !== null ? clone $this->_dispatcherTemplate : null;
        if ($beanDefinition->hasAspects() && $dispatcher !== null) {
            /**
             * @todo the operation of applying an aspect is really expensive!
             */
            foreach ($beanDefinition->getAspects() as $aspect) {
                $this->_applyAspect($aspect, $dispatcher, $rClass, $methods);
            }
        }
        if ($rClass !== false && $this->_aspectManager !== null) {
            foreach ($this->_aspectManager->getAspects() as $aspect) {
                $expression = $aspect->getExpression();
                if (preg_match('/' . $expression . '/', $beanClass) === 0) {
                    $parentClass = $rClass->getParentClass();
                    while($parentClass !== false) {
                        if (preg_match('/' . $expression . '/', $parentClass->getName()) > 0) {
                            $this->_applyAspect($aspect, $dispatcher, $rClass, $methods);
                            break;
                        }
                        $parentClass = $parentClass->getParentClass();
                    }
                    continue;
                }
                $this->_applyAspect($aspect, $dispatcher, $rClass, $methods);
            }
            if (!empty($methods)) {
                $beanClass = $this->_proxyFactory->create($beanClass, $methods, $dispatcher);
            }
        }
        /* @todo change this to a clone */
        $factoryMethod = $beanDefinition->getFactoryMethod();
        if ($factoryMethod == false || empty($factoryMethod)) {
            $constructor = $this->_reflectionFactory->getClass($beanClass);
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
        $this->assemble($bean, $beanDefinition);
        $initMethod = $beanDefinition->getInitMethod();
        if ($initMethod) {
            $bean->$initMethod();
        }
        $destroyMethod = $beanDefinition->getDestroyMethod();
        if ($destroyMethod) {
            $this->registerShutdownMethod($bean, $destroyMethod);
        }
        $this->_lifecycleManager->afterCreate($bean, $beanDefinition);
        return $bean;
    }

    /**
     * Assembles a bean (setter injection)
     *
     * @param mixed $bean
     * @param BeanDefinition $beanDefinition
     *
     * @return void
     */
    protected function assemble($bean, BeanDefinition $beanDefinition)
    {
        $this->_lifecycleManager->beforeAssemble($bean, $beanDefinition);
        foreach ($beanDefinition->getProperties() as $property) {
            $propertyName = $property->getName();
            $methodName = 'set' . ucfirst($propertyName);
            $rClass = $this->_reflectionFactory->getClass($beanDefinition->getClass());
            if ($rClass->hasMethod($methodName)) {
                $bean->$methodName($this->_loadArgOrProperty($property));
            }
        }
        $this->fillAware($beanDefinition, $bean);
        $this->_lifecycleManager->afterAssemble($bean, $beanDefinition);
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
        if ($beanDefinition->isAbstract()) {
            throw new BeanFactoryException(
            	"Cant instantiate abstract bean: $name"
            );
        }
        if ($beanDefinition->isPrototype()) {
            $ret = $this->_createBean($beanDefinition);
        } else if ($beanDefinition->isSingleton()) {
            if (isset($this->_beans[$beanName])) {
                $ret = $this->_beans[$beanName];
            } else {
                $ret = $this->_beanCache->fetch($beanName, $result);
                if (!$ret) {
                    $ret = $this->_createBean($beanDefinition);
                }
                $this->_beans[$beanName] = $ret;
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

    /**
     *
     * Enter description here ...
     * @param unknown_type $messageSource
     */
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
     * @see Ding\Container.IContainer::eventDispatch()
     */
    public function eventDispatch($eventName, $data = null)
    {
        $eventName = 'on' . ucfirst($eventName);
        if (isset($this->_eventListeners[$eventName])) {
            foreach ($this->_eventListeners[$eventName] as $beanName) {
                $bean = $this->getBean($beanName);
                $bean->$eventName($data);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::eventListen()
     */
    public function eventListen($eventName, $beanName)
    {
        if (!isset($this->_eventListeners[$eventName])) {
            $this->_eventListeners[$eventName] = array();
        }
        $eventName = 'on' . ucfirst($eventName);
        $this->_eventListeners[$eventName][] = $beanName;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::registerBeanDefinitionProvider()
     */
    public function registerBeanDefinitionProvider(IBeanDefinitionProvider $provider)
    {
        $this->_beanDefinitionProviders[] = $provider;
    }

    /**
     * If we dont have a ReflectionFactory yet (i.e: didnt make the call to
     * getBean() yet), replace it with this one.
     *
     * @param string $class The name of a class.
     *
     * @return ReflectionClass
     */
    protected function getClass($class)
    {
        return new \ReflectionClass($class);
    }

    /**
     * Will look for "aware" kind of interfaces and inject whatever necessary.
     *
     * @param BeanDefinition $def The Bean Definition
     * @param object $bean The bean
     *
     * @return void
     */
    public function fillAware(BeanDefinition $def, $bean)
    {
        $class = get_class($bean);
        $rClass = $this->_reflectionFactory->getClass($class);
        if ($rClass->implementsInterface('Ding\Reflection\IReflectionFactoryAware')) {
            $bean->setReflectionFactory($this->_reflectionFactory);
        }
        if ($rClass->implementsInterface('Ding\Bean\IBeanNameAware')) {
            $bean->setBeanName($def->getName());
        }
        if ($rClass->implementsInterface('Ding\Logger\ILoggerAware')) {
            $bean->setLogger(\Logger::getLogger($class));
        }
        if ($rClass->implementsInterface('Ding\Container\IContainerAware')) {
            $bean->setContainer($this);
        }
        if ($rClass->implementsInterface('Ding\Resource\IResourceLoaderAware')) {
            $bean->setResourceLoader($this);
        }
        if ($rClass->implementsInterface('Ding\Aspect\IAspectManagerAware')) {
            $bean->setAspectManager($this->_aspectManager);
        }
        if ($rClass->implementsInterface('Ding\Bean\IBeanDefinitionProvider')) {
            $this->registerBeanDefinitionProvider($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterConfigListener')) {
            $this->_lifecycleManager->addAfterConfigListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterDefinitionListener')) {
            $this->_lifecycleManager->addAfterDefinitionListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IBeforeCreateListener')) {
            $this->_lifecycleManager->addBeforeCreateListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterCreateListener')) {
            $this->_lifecycleManager->addAfterCreateListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IBeforeAssembleListener')) {
            $this->_lifecycleManager->addBeforeAssembleListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterAssembleListener')) {
            $this->_lifecycleManager->addAfterAssembleListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Aspect\IAspectProvider')) {
            $this->_aspectManager->registerAspectProvider($bean);
        }
        if ($rClass->implementsInterface('Ding\Aspect\IPointcutProvider')) {
            $this->_aspectManager->registerPointcutProvider($bean);
        }
    }

    /**
     * Called when a signal is caught.
     *
     * @param integer $signo
     *
     * @return void
     */
    public function signalHandler($signo)
    {
        $msg = "Caught Signal: $signo";
        $this->_logger->warn($msg);
        $this->eventDispatch('dingSignal', $signo);
    }

    /**
     * Called by php after set_error_handler()
     *
     * @param integer $type
     * @param string $message
     * @param string $file
     * @param integer $line
     *
     * @return true
     */
    public function errorHandler($type, $message, $file, $line)
    {
        $msg = "$message in $file:$line";
        if ($msg == $this->_lastErrorMessage) {
            return;
        }
        $this->_lastErrorMessage = $msg;
        $this->_logger->error($msg);
        $this->eventDispatch(
            'dingError', new ErrorInfo($type, $message, $file, $line)
        );
        return true;
    }

    // @codeCoverageIgnoreStart
    /**
     * Called by the vm after register_shutdown_function()
     *
     * @return void
     */
    public function shutdownHandler()
    {
        $msg = "Shutting down";
        $this->eventDispatch('dingShutdown');
    }
    // @codeCoverageIgnoreEnd

    /**
     * Constructor.
     *
     * @param array $options options.
     *
     * @return void
     */
    protected function __construct(array $options)
    {
        // Setup logger.
        $this->_logger = \Logger::getLogger(get_class($this));
        $this->_logDebugEnabled = $this->_logger->isDebugEnabled();

        $soullessArray = array();
        $this->_beanAliases = $soullessArray;
        $this->_beanDefs = $soullessArray;
        $this->_beans = $soullessArray;
        $this->_shutdowners = $soullessArray;
        $this->_resources = $soullessArray;
        $this->_eventListeners = $soullessArray;

        // Merge options with our defaults.
        self::$_options = array_replace_recursive(self::$_options, $options);
        $sapi = php_sapi_name();
        if ($sapi == 'cgi' || $sapi == 'cli') {
            $handler = array($this, 'signalHandler');
            foreach ($this->_signals as $signal) {
                pcntl_signal($signal, $handler);
            }
            pcntl_sigprocmask(SIG_UNBLOCK, $this->_signals);
        }
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdownHandler'));

        // We need a lifecycle manager.
        $this->_lifecycleManager = new BeanLifecycleManager;
        $this->_beanDefCache = DummyCacheImpl::getInstance();
        $this->_beanCache = DummyCacheImpl::getInstance();
        $this->registerBeanDefinitionProvider(new Core(self::$_options));
        $this->_reflectionFactory = $this;
        $this->_reflectionFactory = $this->getBean('dingReflectionFactory');
        $this->_proxyFactory = $this->getBean('dingProxyFactory');
        $this->_beanDefCache = $this->getBean('dingDefinitionsCache');
        $this->_beanCache = $this->getBean('dingBeanCache');
        $this->_lifecycleManager = $this->getBean('dingLifecycleManager');
        $this->_aspectManager = $this->getBean('dingAspectManager');
        $this->_dispatcherTemplate = $this->getBean('dingAspectCallDispatcher');

        // Set drivers
        if (isset(self::$_options['bdef']['xml'])) {
            $xmlDriver = $this->getBean('dingXmlBeanDefinitionProvider');
        }
        if (isset(self::$_options['bdef']['yaml'])) {
            $yamlDriver = $this->getBean('dingYamlBeanDefinitionProvider');
        }
        $this->getBean('dingPropertiesDriver');
        $this->getBean('dingMessageSourceDriver');
        $this->getBean('dingMethodInjectionDriver');

        // All set, continue.
        if (isset(self::$_options['bdef']['annotation'])) {
            $anDriver = $this->getBean('dingAnnotationBeanDefinitionProvider');
            $this->getBean('dingAnnotationAspectDriver');
            $this->getBean('dingAnnotationResourceDriver');
            $this->getBean('dingAnnotationInitDestroyMethodDriver');
            $this->getBean('dingAnnotationRequiredDriver');
            $this->getBean('dingMvcAnnotationDriver');
        }
        $this->_lifecycleManager->afterConfig();
    }
}
