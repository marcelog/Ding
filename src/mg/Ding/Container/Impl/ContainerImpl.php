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
     * Properties configured when instantiating the container and by others,
     * like when using a PropertiesHolder.
     *
     * @var string[]
     */
    private $_properties;

    /**
     * Horrible state keeper for getBeanDefinition() to avoid following cyclic
     * dependencies.
     * @var string[]
     */
    private $_definitionsInProcess = array();

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
    public function getBeansByClass($class)
    {
        $cacheKey = "$class.knownByClass";
        $ret = $this->_beanDefCache->fetch($cacheKey, $result);
        if ($result === true) {
            return $ret;
        }
        $ret = array();
        foreach ($this->_beanDefinitionProviders as $provider) {
            $ret = array_merge($ret, $provider->getBeansByClass($class));
        }
        $this->_beanDefCache->store($cacheKey, $ret);
        return $ret;
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
        if (isset($this->_beanAliases[$name])) {
            $name = $this->_beanAliases[$name];
        }
        if (isset($this->_beanDefs[$name])) {
            return $this->_beanDefs[$name];
        }
        $beanDefinition = null;
        if ($this->_beanDefCache !== null) {
            $beanDefinition = $this->_beanDefCache->fetch($name, $result);
        }
        if ($beanDefinition) {
            $this->_beanDefs[$name] = $beanDefinition;
            return $beanDefinition;
        }
        foreach ($this->_beanDefinitionProviders as $provider) {
            $beanDefinition = $provider->getBeanDefinition($name);
            if ($beanDefinition) {
                $beanDefinition->setClass($this->_searchAndReplaceProperties(
                    $beanDefinition->getClass()
                ));
                break;
            }
        }
        if (!$beanDefinition) {
            throw new BeanFactoryException('Unknown bean: ' . $name);
        }
        $beanDefinition = $this->_lifecycleManager->afterDefinition($beanDefinition);
        $this->_beanDefs[$name] = $beanDefinition;
        $this->_beanDefCache->store($name, $beanDefinition);
        foreach ($beanDefinition->getAliases() as $alias) {
            $this->_beanAliases[$alias] = $name;
        }
        return $beanDefinition;
    }

    /**
     * Will try to search and replace the properties found in the given
     * value.
     *
     * @param string $value
     *
     * @return string
     */
    private function _searchAndReplaceProperties($value)
    {
        if (is_string($value)) {
            foreach ($this->_properties as $k => $v) {
                if (strpos($value, $k) !== false) {
                    if (is_string($v)) {
                        $value = str_replace($k, $v, $value);
                    } else {
                        $value = $v;
                        // Assigned value is not a string, so we cant use
                        // strpos anymore on it (i.e: cant continue replacing)
                        break;
                    }
                }
            }
        }
        return $value;
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
        $value = $this->_searchAndReplaceProperties($value);
        if (is_string($value) && strpos($value, 'resource://') === 0) {
            $value = substr($value, 11);
            return $this->getResource($value);
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
    private function _getValueFromDefinition($what)
    {
        $value = null;
        if ($what->isBean()) {
            $value = $this->getBean($what->getValue());
        } else if ($what->isArray()) {
            $value = array();
            foreach ($what->getValue() as $k => $v) {
                $value[$k] = $this->_getValueFromDefinition($v);
            }
        } else if ($what->isCode()) {
            $value = eval($what->getValue());
        } else {
            $value = $this->_loadValue($what->getValue());
        }
        return $value;
    }

    /**
     * Resolves all values for constructor arguments definitions in a
     * bean definition.
     *
     * @param BeanDefinition $definition
     *
     * @return object
     */
    private function _getConstructorValuesForDefinition($definition)
    {
        $args = array();
        foreach ($definition->getArguments() as $argument) {
            $value = $this->_getValueFromDefinition($argument);
            if ($argument->hasName()) {
                $name = $argument->getName();
                $args[$name] = $value;
            } else {
                $args[] = $value;
            }
        }
        return $args;
    }

    /**
     * Instantiates a bean using the constructor.
     *
     * @param BeanDefinition $definition
     *
     * @return object
     */
    private function _instantiateByConstructor(BeanDefinition $definition)
    {
        $class = $definition->getClass();
        if ($definition->hasProxyClass()) {
            $class = $definition->getProxyClassName();
        }
        $rClass = $this->_reflectionFactory->getClass($class);
        $factoryMethod = $rClass->getConstructor();
        if ($factoryMethod !== null) {
            $args = $this->_sortArgsWithNames($definition, $factoryMethod);
            if (empty($args)) {
                return $rClass->newInstanceArgs();
            } else {
                return $rClass->newInstanceArgs($args);
            }
        } else {
            return $rClass->newInstanceArgs();
        }
    }

    /**
     * Instantiates a bean using a factory class.
     *
     * @param BeanDefinition $definition
     *
     * @return object
     */
    private function _instantiateByFactoryClass(BeanDefinition $definition)
    {
        $class = $definition->getClass();
        $rClass = $this->_reflectionFactory->getClass($class);
        $factoryMethodName = $definition->getFactoryMethod();
        $factoryMethod = $rClass->getMethod($factoryMethodName);
        $args = $this->_sortArgsWithNames($definition, $factoryMethod);
        return forward_static_call_array(array($class, $factoryMethodName), $args);
    }

    /**
     * Instantiates a bean using a factory bean.
     *
     * @param BeanDefinition $definition
     *
     * @return object
     */
    private function _instantiateByFactoryBean(BeanDefinition $definition)
    {
        $factoryBean = $this->getBean($definition->getFactoryBean());
        $refObject = new \ReflectionObject($factoryBean);
        $factoryMethod = $refObject->getMethod($definition->getFactoryMethod());
        $args = $this->_sortArgsWithNames($definition, $factoryMethod);
        return $factoryMethod->invokeArgs($factoryBean, $args);
    }

    private function _sortArgsWithNames(BeanDefinition $definition, \ReflectionMethod $rMethod)
    {
        $args = $this->_getConstructorValuesForDefinition($definition);
        $callArgs = array();
        foreach ($rMethod->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            if (isset($args[$parameterName])) {
                $callArgs[] = $args[$parameterName];
                unset($args[$parameterName]);
            }
        }
        foreach ($args as $value) {
            $callArgs[] = $value;
        }
        return $callArgs;
    }

    /**
     * Instantiates a bean.
     *
     * @param BeanDefinition $definition
     *
     * @return object
     */
    private function _instantiate(BeanDefinition $definition)
    {
        if ($definition->isCreatedByConstructor()) {
            return $this->_instantiateByConstructor($definition);
        } else if ($definition->isCreatedWithFactoryBean()) {
            return $this->_instantiateByFactoryBean($definition);
        } else {
            return $this->_instantiateByFactoryClass($definition);
        }
    }

    /**
     * Creates whatever beans this definition depends on.
     *
     * @return void
     */
    private function _createBeanDependencies(BeanDefinition $definition)
    {
        foreach ($definition->getDependsOn() as $depBean) {
            $this->getBean(trim($depBean));
        }
    }

    /**
     * Will inject into the given dispatcher the necessary information to
     * aspects will be run correctly.
     *
     * @throws BeanFactoryException
     * @return void
     */
    private function _applyAspect(
        $targetClass, AspectDefinition $aspectDefinition, IDispatcher $dispatcher
    ) {
        $rClass = $this->_reflectionFactory->getClass($targetClass);
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
     * Applies all aspects specifically defined for this bean definition.
     *
     * @param BeanDefinition $definition
     * @param IDispatcher $dispatcher
     *
     * @return void
     */
    private function _applySpecificAspects(BeanDefinition $definition, IDispatcher $dispatcher)
    {
        if ($definition->hasAspects()) {
            foreach ($definition->getAspects() as $aspect) {
                $this->_applyAspect($definition->getClass(), $aspect, $dispatcher);
            }
        }
    }

    /**
     * Looks for any global aspects that may apply to this bean and applies them.
     *
     * @param BeanDefinition $definition
     * @param IDispatcher $dispatcher
     *
     * @return void
     */
    private function _applyGlobalAspects(BeanDefinition $definition, IDispatcher $dispatcher)
    {
        $class = $definition->getClass();
        $rClass = $this->_reflectionFactory->getClass($class);
        foreach ($this->_aspectManager->getAspects() as $aspect) {
            $expression = $aspect->getExpression();
            if (preg_match('/' . $expression . '/', $class) === 0) {
                $parentClass = $rClass->getParentClass();
                while($parentClass !== false) {
                    if (preg_match('/' . $expression . '/', $parentClass->getName()) > 0) {
                        $this->_applyAspect($class, $aspect, $dispatcher);
                    }
                    $parentClass = $parentClass->getParentClass();
                }
            } else {
                $this->_applyAspect($class, $aspect, $dispatcher);
            }
        }
    }

    /**
     * Applies specific bean aspects and global defined aspects.
     *
     * @param BeanDefinition $definition
     *
     * @return void
     */
    private function _applyAspects(BeanDefinition $definition)
    {
        $class = $definition->getClass();
        $dispatcher = clone $this->_dispatcherTemplate;
        $this->_applySpecificAspects($definition, $dispatcher);
        $this->_applyGlobalAspects($definition, $dispatcher);
        if ($dispatcher->hasMethodsIntercepted()) {
            $definition->setProxyClassName(
                $this->_proxyFactory->create($class, $dispatcher)
            );
        }
    }
    /**
     * This will create a new bean, injecting all properties and applying all
     * aspects.
     *
     * @throws BeanFactoryException
     * @return object
     */
    private function _createBean(BeanDefinition $definition)
    {
        $name = $definition->getName();
        if (isset($this->_definitionsInProcess[$name])) {
            throw new BeanFactoryException(
            	"Cyclic dependency found for: $name"
            );
        }
        $this->_definitionsInProcess[$name] = '';
        $this->_lifecycleManager->beforeCreate($definition);
        $this->_createBeanDependencies($definition);
        $this->_applyAspects($definition);
        $bean = $this->_instantiate($definition);
        if (!is_object($bean)) {
            unset($this->_definitionsInProcess[$name]);
            throw new BeanFactoryException(
            	'Could not instantiate ' . $definition->getName()
            );
        }
        $this->_assemble($bean, $definition);
        $this->_setupInitAndShutdown($bean, $definition);
        $this->_lifecycleManager->afterCreate($bean, $definition);
        unset($this->_definitionsInProcess[$name]);
        return $bean;
    }

    /**
     * Calls init method and register shutdown method.
     *
     * @param object $bean
     * @param BeanDefinition $definition
     *
     * @return void
     */
    private function _setupInitAndShutdown($bean, BeanDefinition $definition)
    {
        if ($definition->hasInitMethod()) {
            $initMethod = $definition->getInitMethod();
            $bean->$initMethod();
        }
        if ($definition->hasDestroyMethod()) {
            $destroyMethod = $definition->getDestroyMethod();
            $this->registerShutdownMethod($bean, $destroyMethod);
        }
    }

    /**
     * Tries to inject by looking up set* methods.
     *
     * @param object $bean
     * @param string $name
     * @param string $value
     *
     * @return boolean
     */
    private function _setterInject($bean, $name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        $rClass = $this->_reflectionFactory->getClass(get_class($bean));
        if ($rClass->hasMethod($methodName)) {
            $bean->$methodName($value);
            return true;
        }
        return false;
    }

    /**
     * Tries to inject by looking up a method of the given name.
     *
     * @param object $bean
     * @param string $name
     * @param string $value
     *
     * @return boolean
     */
    private function _nonSetterMethodInject($bean, $name, $value)
    {
        $rClass = $this->_reflectionFactory->getClass(get_class($bean));
        if ($rClass->hasMethod($name)) {
            $bean->$name($value);
            return true;
        }
        return false;
    }

    /**
     * Tries to inject by looking up a property by name.
     *
     * @param object $bean
     * @param string $name
     * @param string $value
     *
     * @return boolean
     */
    private function _propertyInject($bean, $name, $value)
    {
        $rClass = $this->_reflectionFactory->getClass(get_class($bean));
        if ($rClass->hasProperty($name)) {
             $rProperty = $rClass->getProperty($name);
             if (!$rProperty->isPublic()) {
                $rProperty->setAccessible(true);
             }
            $rProperty->setValue($bean, $value);
            return true;
        }
        return false;
    }
    /**
     * Assembles a bean (setter injection)
     *
     * @param mixed $bean
     * @param BeanDefinition $beanDefinition
     *
     * @return void
     */
    private function _assemble($bean, BeanDefinition $beanDefinition)
    {
        $this->_lifecycleManager->beforeAssemble($bean, $beanDefinition);
        foreach ($beanDefinition->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyValue = $this->_getValueFromDefinition($property);
            if (
                $this->_setterInject($bean, $propertyName, $propertyValue)
                || $this->_propertyInject($bean, $propertyName, $propertyValue)
                || $this->_nonSetterMethodInject($bean, $propertyName, $propertyValue)
            ) {
                continue;
            }
            throw new BeanFactoryException("Dont know how to inject: $propertyName");
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
    public function getMessage($bundle, $message, array $arguments, $locale = 'default')
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
     * @see Ding\Bean.IBeanDefinitionProvider::getBeansListeningOn()
     */
    public function getBeansListeningOn($eventName)
    {
        if (isset($this->_eventListeners[$eventName])) {
            return $this->_eventListeners[$eventName];
        }
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::eventDispatch()
     */
    public function eventDispatch($eventName, $data = null)
    {
        if ($this->_logDebugEnabled) {
            $this->_logger->debug("Dispatching event: $eventName");
        }
        $listeners = $this->getBeansListeningOn($eventName);
        foreach ($this->_beanDefinitionProviders as $provider) {
            $listeners = array_merge($listeners, $provider->getBeansListeningOn($eventName));
        }
        $eventName = 'on' . ucfirst($eventName);
        foreach ($listeners as $beanName) {
            $bean = $this->getBean($beanName);
            $bean->$eventName($data);
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
     * (non-PHPdoc)
     * @see Ding\Container.IContainer::registerProperties()
     */
    public function registerProperties(array $properties)
    {
        foreach ($properties as $key => $value) {
            if (strncmp($key, 'php.', 4) === 0) {
                ini_set(substr($key, 4), $value);
            }
            $this->_properties['${' . $key . '}'] = $value;
        }
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
        $this->_properties = $soullessArray;

        // Merge options with our defaults.
        self::$_options = array_replace_recursive(self::$_options, $options);
        $this->registerProperties(self::$_options['properties']);
        $sapi = php_sapi_name();
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            if ($sapi == 'cgi' || $sapi == 'cli') {
                $signals = array(
                    SIGQUIT, SIGHUP, SIGINT, SIGCHLD, SIGTERM, SIGUSR1, SIGUSR2
                );
                $handler = array($this, 'signalHandler');
                foreach ($signals as $signal) {
                    pcntl_signal($signal, $handler);
                }
                pcntl_sigprocmask(SIG_UNBLOCK, $signals);
            }
        }
        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdownHandler'));

        $this->_lifecycleManager = new BeanLifecycleManager;
        $this->_dispatcherTemplate = new DispatcherImpl();
        $this->_aspectManager = new AspectManager();
        $this->_aspectManager->setCache(DummyCacheImpl::getInstance());
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
            $this->getBean('dingAnnotationDiscovererDriver');
            $this->getBean('dingAnnotationBeanDefinitionProvider');
            $this->getBean('dingAnnotationValueDriver');
            $this->getBean('dingAnnotationResourceDriver');
            $this->getBean('dingAnnotationInjectDriver');
            $this->getBean('dingAnnotationInitDestroyMethodDriver');
            $this->getBean('dingAnnotationRequiredDriver');
            $this->getBean('dingMvcAnnotationDriver');
        }
        $this->_lifecycleManager->afterConfig();
    }
}
