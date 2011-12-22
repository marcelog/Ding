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

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Container\IContainerAware;
use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Container\IContainer;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanAnnotationDefinition;
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
    IContainerAware, IReflectionFactoryAware
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
    private $_configBeansDefinitions = array();
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

    /**
     * Loads a bean definition from the given annotations.
     *
     * @param string                     $name          Candidate name.
     * @param string                     $factoryBean   Factory bean name.
     * @param string                     $factoryMethod Factory bean method.
     * @param BeanAnnotationDefinition[] $annotations   Annotations with data.
     *
     * @return BeanDefinition
     */
    private function _loadBean($name, $factoryBean, $factoryMethod, $annotations, $factory)
    {
        // a @Bean in @Configuration, does not specify class
        $def = $this->_getBeanDefinition($name, 'stdclass', $annotations, $factory);
        $def->setFactoryBean($factoryBean);
        $def->setFactoryMethod($factoryMethod);
        return $def;
    }

    /**
     * Creates a bean definition from the given annotations.
     *
     * @param string                     $name          Bean name.
     * @param string                     $class         Bean class.
     * @param BeanAnnotationDefinition[] $annotations   Annotations with data.
     *
     * @return BeanDefinition
     */
    private function _getBeanDefinition($name, $class, $annotations, $factory)
    {
        $def = null;
        $parent = $this->reflectionFactory->getClass($class)->getParentClass();
        while($parent !== false)
        {
            $parentAnnotations = $this->reflectionFactory->getClassAnnotations($parent->getName());
            if (isset($parentAnnotations['class']['Component'])) {
                $parentNameBean = $this->getName($parentAnnotations['class']['Component']->getArguments());
                $def = $this->_getBeanDefinition($parentNameBean, $parent->getName(), $parentAnnotations['class'], $factory);
                break;
            }
            $parent = $this->reflectionFactory->getClass($parent->getName())->getParentClass();
        };
        if ($def === null) {
            $def = new BeanDefinition('dummy');
        }
        $def->setClass($class);
        if (isset($annotations['Component'])) {
            $beanAnnotation = $annotations['Component'];
        } else {
            // Only @Bean can override name and class arguments
            $beanAnnotation = $annotations['Bean'];
            $overrideName = $beanAnnotation->getArguments();
            if (!empty($overrideName)) {
                if (isset($overrideName['name'])) {
                    $name = $overrideName['name'];
                }
                if (isset($overrideName['class'])) {
                    $def->setClass($overrideName['class']);
                }
            }
        }

        $args = $beanAnnotation->getArguments();
        if (isset($args['name'])) {
            if (is_array($args['name'])) {
                $alias = $args['name'];
                foreach ($alias as $newAlias) {
                    $def->addAlias($newAlias);
                }
            }
        }
        $def->setName($name);
        if (isset($annotations['Scope'])) {
            $args = $annotations['Scope']->getArguments();
            if (isset($args['value'])) {
                if ($args['value'] == 'singleton') {
                    $def->setScope(BeanDefinition::BEAN_SINGLETON);
                } else if ($args['value'] == 'prototype') {
                    $def->setScope(BeanDefinition::BEAN_PROTOTYPE);
                } else {
                    throw new BeanFactoryException('Invalid bean scope: ' . $args['value']);
                }
            }
        }
        if (isset($annotations['InitMethod'])) {
            $args = $annotations['InitMethod']->getArguments();
            if (isset($args['method'])) {
                $def->setInitMethod($args['method']);
            }
        }
        if (isset($annotations['DestroyMethod'])) {
            $args = $annotations['DestroyMethod']->getArguments();
            if (isset($args['method'])) {
                $def->setDestroyMethod($args['method']);
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
        $configClasses = $this->reflectionFactory->getClassesByAnnotation('Configuration');
        foreach ($configClasses as $configClass) {
            $configBeanName = $configClass . 'DingConfigClass';
            $this->_configClasses[$configClass] = $configBeanName;
            $def = false;
            try
            {
                $def = $this->_container->getBeanDefinition($configBeanName);
            } catch (BeanFactoryException $exception) {
                $def = new BeanDefinition($configBeanName);
                $def->setClass($configClass);
                $def->setScope(BeanDefinition::BEAN_SINGLETON);
                $this->_configBeansDefinitions[$configBeanName] = $def;
            }
            $annotations = $this->reflectionFactory->getClassAnnotations($configClass);
            array_shift($annotations);
            foreach ($annotations as  $method => $annotatedBean) {
                $this->registerEventsFor($annotatedBean, $configClass, $this->_container);
            }
        }
        foreach ($this->reflectionFactory->getClassesByAnnotation('Component') as $component) {
            $annotations = $this->reflectionFactory->getClassAnnotations($component);
            $this->registerEventsFor($annotations['class'], $component, $this->_container);
        }
    }

    /**
     * Checks if the given name is the name of the bean contained in $candidateName.
     * $candidateName is the argument name for a given bean, which can be an array
     * or a string.
     *
     * @param string[]|string $candidateName Array of names or string.
     * @param string          $name          The name to test for.
     *
     * @return boolean
     */
    protected function isBean($candidateName, $name)
    {
        if (is_array($candidateName) && in_array($name, $candidateName)) {
            return true;
        } else if($candidateName == $name) {
            return true;
        }
        return false;
    }

    /**
     * Returns a bean name. If the annotations dont have the 'name' attribute,
     * a name will be dynamically generated. If it is an array, the first element
     * is selected.
     *
     * @param string[] $annotatedBean Bean annotations.
     *
     * @return string
     */
    protected function getName(array $annotatedBean)
    {
        if (!isset($annotatedBean['name'])) {
            $beanName = BeanDefinition::generateName('Bean');
        } else if (is_array($annotatedBean['name'])) {
            $beanName = array_shift($annotatedBean['name']);
        } else {
            $beanName = $annotatedBean['name'];
        }
        return $beanName;
    }

    /**
     * Looks for @ListensOn and register the bean as an event listener. Since
     * this is an "early" discovery of a bean, a BeanDefinition is generated.
     *
     * @param string[]     $annotatedBean Bean annotations
     * @param string       $class         Class name
     * listener
     *
     * @return void
     */
    protected function registerEventsFor(array $annotatedBean, $class, $factory)
    {
        if (isset($annotatedBean['ListensOn'])) {
            $beanName = $this->getName($annotatedBean);
            $def = $this->_getBeanDefinition($beanName, $class, $annotatedBean, $factory);
            $this->_configBeansDefinitions[$beanName] = $def;
            $beanAnnotation = $annotatedBean['ListensOn'];
            $args = $beanAnnotation->getArguments();
            $events = $args['value'];
            if (!is_array($events)) {
                $events = explode(',', $events);
            }
            foreach ($events as $eventName) {
                $eventName = trim($eventName);
                $factory->eventListen($eventName, $beanName);
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
            foreach (
                $this->reflectionFactory->getClassAnnotations($configClass) as $method => $annotations
            ) {
                if ($method == 'class') {
                    continue;
                }
                if (isset($annotations['Bean'])) {
                    $beanAnnotation = $annotations['Bean'];
                    $args = $beanAnnotation->getArguments();
                    if (!isset($args['name'])) {
                        $args['name'] = $method;
                    }
                    if ($this->isBean($args['name'], $name)) {
                        $bean = $this->_loadBean($args['name'], $configBeanName, $method, $annotations, $this->_container);
                        $this->_configBeans[$configBeanName][$name] = $bean;
                    }
                    if ($bean !== null) {
                        break;
                    }
                }
            }
            if ($bean !== null) {
                break;
            }
        }
        // The bean might not be defined in a @Configuration, but with
        // @Component
        if ($bean === null) {
            $components = $this->reflectionFactory->getClassesByAnnotation('Component');
            foreach ($components as $component) {
                $annotations = $this->reflectionFactory->getClassAnnotations($component);
                $args = $annotations['class']['Component']->getArguments();
                if (isset($args['name'])) {
                    if ($this->isBean($args['name'], $name)) {
                        $bean = $this->_getBeanDefinition($args['name'], $component, $annotations['class'], $this->_container);
                        break;
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
        $this->_configBeans = array();
        $classes = get_declared_classes();
        self::$_knownClasses = array_combine($classes, $classes);

    }
}
