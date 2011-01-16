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

    private function _scanClass($class)
    {
        $rClass = ReflectionFactory::getClass($class);
        $ret = array();
        $ret['class'] = array();
        foreach ($this->_getAnnotations($rClass->getDocComment()) as $annotation) {
            $ret['class'][] = $annotation;
        }
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            $ret[$methodName] = array();
            foreach ($this->_getAnnotations($method->getDocComment()) as $annotation) {
                $ret[$methodName][] = $annotation;
            }
        }
        return $ret;
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
                    self::$_knownClasses[$aNewClass] = $this->_scanClass($aNewClass);
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
     * Parses all annotations in the given text.
     *
     * @param string $text
     *
     * @return BeanAnnotationDefinition[]
     */
    private function _getAnnotations($text)
    {
        $ret = array();
        if (preg_match_all('/@.+/', $text, $matches) > 0) {
            foreach ($matches[0] as $annotation) {
                $argsStart = strpos($annotation, '(');
                $arguments = array();
                if ($argsStart != false) {
                    $name = substr($annotation, 1, $argsStart - 1);
                    $args = substr($annotation, $argsStart + 1, -1);
                    // http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
                    $argsN = preg_match_all(
                    	'/([^=,]*)=("[^"]*"|[^,"]*)/', $args, $matches
                    );
                    if ($argsN > 0)
                    {
                        for ($i = 0; $i < $argsN; $i++) {
                            $key = trim($matches[1][$i]);
                            $value = trim($matches[2][$i]);
                            $arguments[$key] = $value;
                        }
                    }
                } else {
                    $name = substr($annotation, 1);
                }
                $ret[] = new BeanAnnotationDefinition($name, $arguments);
            }
        }
        return $ret;
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
        return $bean;
        if ($bean === null) {
            return $bean;
        }
        $class = $bean->getClass();
        if (empty($class)) {
            return $bean;
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
    }
}