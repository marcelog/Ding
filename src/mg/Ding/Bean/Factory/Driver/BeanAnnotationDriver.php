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
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;
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
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class BeanAnnotationDriver implements ILifecycleListener
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
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        return $bean;
    }

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
        foreach (scandir($dir) as $dirEntry) {
            if ($dirEntry == '.' || $dirEntry == '..') {
                continue;
            }
            $dirEntry = $dir . DIRECTORY_SEPARATOR . $dirEntry;
            if (is_dir($dirEntry)) {
                $this->_scan($dirEntry);
            } else if(is_file($dirEntry) && $this->_isScannable($dirEntry)) {
                include_once $dirEntry;
                $newClasses = get_declared_classes();
                foreach (array_diff($newClasses, self::$_knownClasses) as $aNewClass) {
                    self::$_knownClasses[$aNewClass] = ReflectionFactory::getClassAnnotations($aNewClass);
                }
            }
        }
    }

    /**
     * Returns known classes.
     *
	 * @return string[]
     */
    public static function getKnownClasses()
    {
        return self::$_knownClasses;
    }

    private function _loadBean($name, $factoryBean, $annotations)
    {
        $def = new BeanDefinition($name);
        $def->setFactoryBean($factoryBean);
        $def->setFactoryMethod($name);
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
                $factory->setBeanDefinition($configBeanName, $def);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeCreate()
     */
    public function beforeCreate(IBeanFactory $factory, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterCreate()
     */
    public function afterCreate(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * Annotates the given bean with the annotations found in the class and
     * every method.
     *
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     *
     * @return BeanDefinition
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition &$bean = null)
    {
        if ($bean != null) {
            return $bean;
        }
        foreach ($this->_configClasses as $configClass => $configBeanName) {
            if (isset($this->_configBeans[$configBeanName][$beanName])) {
                $bean = $this->_configBeans[$configBeanName][$beanName];
                return $bean;
            }
            if (empty($this->_configBeans[$configBeanName])) {
                $this->_configBeans[$configBeanName] = array();
                foreach (
                    ReflectionFactory::getClassAnnotations($configClass)
                    as $method => $annotations
                ) {
                    if ($method == 'class') {
                        continue;
                    }
                    if ($method == $beanName) {
                        if (isset($annotations['Bean'])) {
                            $bean = $this->_loadBean($method, $configBeanName, $annotations);
                            $this->_configBeans[$configBeanName][$method] = $annotation;
                            return $bean;
                        }
                    }
                }
            }
        }
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeAssemble()
     */
    public function beforeAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterAssemble()
     */
    public function afterAssemble(IBeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::destruct()
     */
    public function destruct($bean, BeanDefinition $beanDefinition)
    {
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
        if (self::$_instance === false) {
            $ret = new BeanAnnotationDriver($options);
            self::$_instance = $ret;
        } else {
            $ret = self::$_instance;
        }
        return $ret;
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
        self::$_knownClasses = get_declared_classes();
    }
}