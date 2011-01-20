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
    private $_scanDirs;
    private static $_knownClasses = false;
    private $_configClasses = false;
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

    }


    private function _scan($dir)
    {
        self::$_knownClasses = get_declared_classes();
        foreach (scandir($dir) as $dirEntry) {
            if ($dirEntry == '.' || $dirEntry == '..') {
                continue;
            }
            $dirEntry = $dir . DIRECTORY_SEPARATOR . $dirEntry;
            if (is_dir($dirEntry)) {
                $this->_scan($dirEntry);
            } else if(is_file($dirEntry)) {
                $extensionPos = strrpos($dirEntry, '.');
                if ($extensionPos === false) {
                    continue;
                }
                if (substr($dirEntry, $extensionPos, 4) != '.php') {
                    continue;
                }
                include_once $dirEntry;
                $newClasses = get_declared_classes();
                foreach (array_diff($newClasses, self::$_knownClasses) as $aNewClass) {
                    self::$_knownClasses[$aNewClass] = ReflectionFactory::getClassAnnotations($aNewClass);
                }
            }
        }
    }

    public static function getKnownClasses()
    {
        return self::$_knownClasses;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        foreach ($this->_scanDirs as $dir) {
            $this->_scan($dir);
        }
        $configClasses = ReflectionFactory::getClassesByAnnotation('Configuration');
        foreach ($configClasses as $configClass) {
            $this->_configClasses[] = $configClass;
            $beanName = $configClass . 'DingConfigClass';
            $def = new BeanDefinition($beanName);
            $def->setClass($configClass);
            $def->setScope(BeanDefinition::BEAN_SINGLETON);
            $factory->setBeanDefinition($beanName, $def);
            $this->_configBeans[$beanName] = array();
            foreach (ReflectionFactory::getClassAnnotations($configClass) as $method => $annotations) {
                if ($method == 'class') {
                    continue;
                }
                foreach ($annotations as $name => $annotation) {
                    if ($name == 'Bean') {
                        $this->_configBeans[$beanName][$method] = $annotation;
                    }
                }
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
        $configClasses = ReflectionFactory::getClassesByAnnotation('Configuration');
        foreach ($this->_configBeans as $class => $beans) {
            if (isset($this->_configBeans[$class][$beanName])) {
                if ($bean === null) {
                    $bean = new BeanDefinition($beanName);
                }
                $args = $this->_configBeans[$class][$beanName]->getArguments();
                $bean->setFactoryBean($class);
                $bean->setFactoryMethod($beanName);
                $bean->setScope(BeanDefinition::BEAN_PROTOTYPE);
                return $bean;
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
    }
}