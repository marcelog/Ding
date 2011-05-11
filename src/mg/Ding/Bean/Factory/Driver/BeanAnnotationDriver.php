<?php
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
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Lifecycle\IBeforeConfigListener;
use Ding\Cache\Locator\CacheLocator;

use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\IBeanFactory;
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
class BeanAnnotationDriver
    implements IBeforeConfigListener, IAfterConfigListener, IBeforeDefinitionListener
{
    /**
     * Holds current instance.
     * @var BeanAnnotationDriver
     */
    private static $_instance = false;

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
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeConfig()
     */
    public function beforeConfig(IBeanFactory $factory)
    {
        foreach ($this->_scanDirs as $dir) {
            $this->_scan($dir);
        }
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
                $classes = ReflectionFactory::getClassesFromCode(@file_get_contents($dirEntry));
                include_once $dirEntry;
                foreach ($classes as $aNewClass) {
                    self::$_knownClasses[$aNewClass] = ReflectionFactory::getClassAnnotations($aNewClass);
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
    private function _loadBean($name, $factoryBean, $factoryMethod, $annotations)
    {
        $def = new BeanDefinition($name);
        $def->setFactoryBean($factoryBean);
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
        $def->setFactoryMethod($factoryMethod);
        if (isset($annotations['Scope'])) {
            $args = $annotations['Scope']->getArguments();
            if (isset($args['value'])) {
                if ($args['value'] == 'singleton') {
                    $def->setScope(BeanDefinition::BEAN_SINGLETON);
                } else if ($args['value'] == 'prototype') {
                    $def->setScope(BeanDefinition::BEAN_PROTOTYPE);
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
    public function afterConfig(IBeanFactory $factory)
    {
        $configClasses = ReflectionFactory::getClassesByAnnotation('Configuration');
        foreach ($configClasses as $configClass) {
            $configBeanName = $configClass . 'DingConfigClass';
            $this->_configClasses[$configClass] = $configBeanName;
            $def = false;
            try
            {
                $def = $factory->getBeanDefinition($configBeanName);
            } catch (BeanFactoryException $exception) {
                $def = new BeanDefinition($configBeanName);
                $def->setClass($configClass);
                $def->setScope(BeanDefinition::BEAN_SINGLETON);
                $properties = array();
                $annotations = ReflectionFactory::getClassAnnotations($configClass);
                /*
                if (isset($annotations['class']['properties'])) {
                    foreach ($annotations['class']['properties'] as $property => $propAnnotations) {
                        foreach ($propAnnotations as $propAnnotation) {
                            if ($propAnnotation->getName() == 'Resource') {
                                $properties[] = new BeanPropertyDefinition(
                                    $property, BeanPropertyDefinition::PROPERTY_BEAN, $property
                                );
                            }
                        }
                    }
                    $def->setAutowiredProperties($properties);
                }
                */
                $factory->setBeanDefinition($configBeanName, $def);
            }
        }
    }

    /**
     * Annotates the given bean with the annotations found in the class and
     * every method.
     *
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     *
     * @return BeanDefinition
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition $bean = null)
    {
        // This should not be necessary because this driver is the first one
        // loaded by the container.
        //if ($bean != null) {
        //    return $bean;
        //}
        foreach ($this->_configClasses as $configClass => $configBeanName) {
            // This should not be necessary because the container and the lifecycle
            // impose no more than one beforeDefinition() call once the bean is
            // gone out of the afterDefinition() phase.
            //if (isset($this->_configBeans[$configBeanName][$beanName])) {
            //    $bean = $this->_configBeans[$configBeanName][$beanName];
            //    return $bean;
            //}
            if (empty($this->_configBeans[$configBeanName])) {
                $this->_configBeans[$configBeanName] = array();
            }
            foreach (
                ReflectionFactory::getClassAnnotations($configClass) as $method => $annotations
            ) {
                if ($method == 'class') {
                    continue;
                }
                if (isset($annotations['Bean'])) {
                    $beanAnnotation = $annotations['Bean'];
                    $args = $beanAnnotation->getArguments();
                    if (isset($args['name'])) {
                        $name = $args['name'];
                    } else {
                        $name = $method;
                    }
                    if ($name == $beanName) {
                        if (isset($annotations['Bean'])) {
                            $bean = $this->_loadBean($name, $configBeanName, $method, $annotations);
                            $this->_configBeans[$configBeanName][$name] = $bean;
                        }
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
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return BeanAnnotationDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new BeanAnnotationDriver($options);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @param array $options Optional options.
     *
     * @return void
     */
    private function __construct(array $options)
    {
        $this->_scanDirs = $options['scanDir'];
        $this->_configClasses = array();
        $this->_configBeans = array();
        $classes = get_declared_classes();
        self::$_knownClasses = array_combine($classes, $classes);
        $this->_cache = CacheLocator::getAnnotationsCacheInstance();
    }
}