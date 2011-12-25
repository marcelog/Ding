<?php
/**
 * This driver will look up all annotations for the class and each method of
 * the class (of the bean, of course).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Provider
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
namespace Ding\Bean\Provider;

use Ding\Annotation\Collection;
use Ding\Annotation\Annotation as AnnotationDefinition;
use Ding\Logger\ILoggerAware;

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Container\IContainerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Container\IContainer;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Reflection\IReflectionFactory;
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This driver will look up all annotations for the class and each method of
 * the class (of the bean, of course).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Annotation
    implements IAfterConfigListener, IBeanDefinitionProvider,
    IContainerAware, IReflectionFactoryAware, ILoggerAware
{
    protected $container;

    /**
     * Target directories to scan for annotated classes.
     * @var string[]
     */
    private $_scanDirs;

    /**
     * Known classes.
     * @var string[]
     */
    private static $_knownClasses = false;

    /**
     * @Configuration annotated classes.
     * @var string[]
     */
    private $_configClasses = false;

    /**
     * @Configuration beans (coming from @Configuration annotated classes).
     * @var object[]
     */
    private $_configBeans = false;

    /**
     * Our cache.
     * @var ICache
     */
    private $_cache = false;

    /**
     * Definitions for config beans.
     * @var BeanDefinition[]
     */
    private $_beanDefinitions = array();
    private $_myBeanNames = array();

    /**
     * Container.
     * @var IContainer
     */
    private $_container;

    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    private $_logger;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }

    /**
     * Returns true if the given filesystem entry is interesting to scan.
     *
     * @param string $dirEntry Filesystem entry.
     */
    private function _isScannable($dirEntry)
    {
        $extensionPos = strrpos($dirEntry, '.');
        if ($extensionPos === false) {
            return false;
        }
        if (substr($dirEntry, $extensionPos, 4) != '.php') {
            return false;
        }
        return true;
    }

    /**
     * Recursively scans a directory looking for annotated classes.
     *
     * @param string $dir Directory to scan.
     *
     * @return void
     */
    private function _scan($dir)
    {
        $result = false;
        $cacheKey = str_replace('\\', '_', str_replace('/', '_', $dir)) . '.knownclasses';
        $knownClasses = $this->_cache->fetch($cacheKey, $result);
        if ($result === true) {
            self::$_knownClasses = array_merge_recursive(self::$_knownClasses, $knownClasses);
            foreach ($knownClasses as $class => $v) {
                $result = false;
                $file = $this->_cache->fetch(str_replace('\\', '_', $class) . '.include_file', $result);
                if ($result === true) {
                    include_once $file;
                }
            }
            return;
        }
        foreach (scandir($dir) as $dirEntry) {
            if ($dirEntry == '.' || $dirEntry == '..') {
                continue;
            }
            $dirEntry = $dir . DIRECTORY_SEPARATOR . $dirEntry;
            if (is_dir($dirEntry)) {
                $this->_scan($dirEntry);
            } else if(is_file($dirEntry) && $this->_isScannable($dirEntry)) {
                $classes = $this->reflectionFactory->getClassesFromCode(@file_get_contents($dirEntry));
                include_once $dirEntry;
                foreach ($classes as $aNewClass) {
                    self::$_knownClasses[$aNewClass] = $this->reflectionFactory->getClassAnnotations($aNewClass);
                    $classes = array_combine($classes, $classes);
                    $include_files[$aNewClass] = $dirEntry;
                    $this->_cache->store(str_replace('\\', '_', $aNewClass) . '.include_file', $dirEntry);
                }
                $this->_cache->store($cacheKey, $classes);
            }
        }
    }

    public function setLogger(\Logger $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Creates a bean definition from the given annotations.
     *
     * @param string $name Bean name.
     * @param string $class Bean class.
     * @param Collection $annotations Annotations with data.
     *
     * @return BeanDefinition
     */
    private function _getBeanDefinition($name, $class, Collection $annotations)
    {
        $def = null;
        $parentRefClass = $this->reflectionFactory->getClass($class)->getParentClass();
        while($parentRefClass !== false)
        {
            $parentAnnotations = $this->reflectionFactory->getClassAnnotations($parentRefClass->getName());
            if ($parentAnnotations->contains('component')) {
                $annotation = $parentAnnotations->getSingleAnnotation('component');
                $parentNameBean = $this->getOrCreateName($annotation);
                $def = $this->_getBeanDefinition($parentNameBean, $parentRefClass->getName(), $parentAnnotations);
                break;
            }
            $parentRefClass = $this->reflectionFactory->getClass($parentRefClass->getName())->getParentClass();
        };
        if ($def === null) {
            $def = new BeanDefinition('dummy');
        }
        $def->setClass($class);
        if ($annotations->contains('component')) {
            $beanAnnotation = $annotations->getSingleAnnotation('component');
        } else if ($annotations->contains('bean')) {
            $beanAnnotation = $annotations->getSingleAnnotation('bean');
            if ($beanAnnotation->hasOption('class')) {
                $def->setClass($beanAnnotation->getOptionSingleValue('class'));
            }
        }
        if ($beanAnnotation->hasOption('name')) {
            $names = $beanAnnotation->getOptionValues('name');
            foreach ($names as $alias) {
                $def->addAlias($alias);
            }
        }
        $def->setName($name);
        if ($annotations->contains('scope')) {
            $annotation = $annotations->getSingleAnnotation('scope');
            if ($annotation->hasOption('value')) {
                $scope = $annotation->getOptionSingleValue('value');
                if ($scope == 'singleton') {
                    $def->setScope(BeanDefinition::BEAN_SINGLETON);
                } else if ($scope == 'prototype') {
                    $def->setScope(BeanDefinition::BEAN_PROTOTYPE);
                } else {
                    throw new BeanFactoryException("Invalid bean scope: $scope");
                }
            }
        }
        if ($annotations->contains('initmethod')) {
            $annotation = $annotations->getSingleAnnotation('initmethod');
            if ($annotation->hasOption('method')) {
                $def->setInitMethod($annotation->getOptionSingleValue('method'));
            }
        }
        if ($annotations->contains('destroymethod')) {
            $annotation = $annotations->getSingleAnnotation('destroymethod');
            if ($annotation->hasOption('method')) {
                $def->setDestroyMethod($annotation->getOptionSingleValue('method'));
            }
        }
        return $def;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig()
    {
        foreach ($this->_scanDirs as $dir) {
            $this->_scan($dir);
        }
        $configClasses = $this->reflectionFactory->getClassesByAnnotation('configuration');
        foreach ($configClasses as $class) {
            $configBeanName = $class . 'DingConfigClass';
            $this->_configClasses[$class] = $configBeanName;
            $def = new BeanDefinition($configBeanName);
            $def->setClass($class);
            $this->_configBeansDefinitions[$configBeanName] = $def;
            $this->_configBeansDefinitions[$configBeanName] = $this->_container->getBeanDefinition($configBeanName);
            $rClass = $this->reflectionFactory->getClass($class);
            $classAnnotations = $this->reflectionFactory->getClassAnnotations($class);

            $this->registerEventsFor($classAnnotations, $configBeanName, $class);
            foreach ($rClass->getMethods() as $method) {
                $methodName = $method->getName();
                $annotations = $this->reflectionFactory->getMethodAnnotations($class, $methodName);
                if ($annotations->contains('bean')) {
                    $beanName = $this->getOrCreateName($annotations->getSingleAnnotation('bean'));
                    $this->registerEventsFor($annotations, $beanName, $class);
                }
            }
        }
        foreach ($this->reflectionFactory->getClassesByAnnotation('component') as $component) {
            $annotations = $this->reflectionFactory->getClassAnnotations($component);
            $beanName = $this->getOrCreateName($annotations->getSingleAnnotation('component'));
            $this->registerEventsFor($annotations, $beanName, $component);
        }
    }

    /**
     * Returns a bean name. If the annotations dont have the 'name' attribute,
     * a name will be dynamically generated. If it is an array, the first element
     * is selected.
     *
     * @param Annotation $annotation Bean annotation (@Bean, @Component, etc)
     *
     * @return string
     */
    protected function getOrCreateName(AnnotationDefinition $annotation)
    {
        if ($annotation->hasOption('name')) {
            return $annotation->getOptionSingleValue('name');
        }
        return BeanDefinition::generateName('Bean');
    }

    /**
     * Looks for @ListensOn and register the bean as an event listener. Since
     * this is an "early" discovery of a bean, a BeanDefinition is generated.
     *
     * @param Collection $annotations Bean Annotations (for classes or methods)
     * @param string $beanName The target bean name.
     * @param string $class The bean class
     *
     * @return void
     */
    protected function registerEventsFor(Collection $annotations, $beanName, $class)
    {
        if ($annotations->contains('listenson')) {
            $def = $this->_getBeanDefinition($beanName, $class, $annotations);
            $this->_configBeansDefinitions[$beanName] = $def;
            $annotation = $annotations->getSingleAnnotation('listenson');
            foreach ($annotation->getOptionValues('value') as $eventCsv) {
                foreach (explode(',', $eventCsv) as $eventName) {
                    $this->_container->eventListen($eventName, $beanName);
                }
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($name)
    {
        $bean = null;
        if (isset($this->_configBeansDefinitions[$name])) {
            return $this->_configBeansDefinitions[$name];
        }
        foreach ($this->_configClasses as $configClass => $configBeanName) {
            if (empty($this->_configBeans[$configBeanName])) {
                $this->_configBeans[$configBeanName] = array();
            }
            $rClass = $this->reflectionFactory->getClass($configClass);
            foreach ($rClass->getMethods() as $method) {
                $methodName = $method->getName();
                $annotations = $this->reflectionFactory->getMethodAnnotations($configClass, $methodName);
                if ($annotations->contains('bean')) {
                    $annotation = $annotations->getSingleAnnotation('bean');
                    if ($annotation->hasOption('name')) {
                        $beanNames = $annotation->getOptionValues('name');
                    } else {
                        $beanNames = array($methodName);
                    }
                    if (in_array($name, $beanNames)) {
                        $bean = $this->_getBeanDefinition($name, 'stdclass', $annotations);
                        $bean->setFactoryBean($configBeanName);
                        $bean->setFactoryMethod($methodName);
                        $this->_configBeansDefinitions[$name] = $bean;
                        return $bean;
                    }
                }
            }
        }
        // The bean might not be defined in a @Configuration, but with
        // @Component
        $components = $this->reflectionFactory->getClassesByAnnotation('component');
        foreach ($components as $component) {
            $annotations = $this->reflectionFactory->getClassAnnotations($component);
            if ($annotations->contains('component')) {
                $annotation = $annotations->getSingleAnnotation('component');
                if ($annotation->hasOption('name')) {
                    $beanNames = $annotation->getOptionValues('name');
                    if (in_array($name, $beanNames)) {
                        return $this->_getBeanDefinition($name, $component, $annotations);
                    }
                }
            }
        }
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Aspect.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeanDefinitionByClass($class)
    {
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainerAware::setContainer()
     */
    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }

    public function setCache(\Ding\Cache\ICache $cache)
    {
        $this->_cache = $cache;
    }
    /**
     * Constructor.
     *
     * @param array              $options Optional options.
     * @param \Ding\Cache\ICache $cache   Annotations cache.
     *
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_scanDirs = $options['scanDir'];
        $this->_configClasses = array();
        $this->_beanDefinitions = array();
        $this->_configBeans = array();
        $classes = get_declared_classes();
        self::$_knownClasses = array_combine($classes, $classes);

    }
}
