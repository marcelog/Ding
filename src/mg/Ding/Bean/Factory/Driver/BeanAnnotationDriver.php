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
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition &$bean)
    {
        return $bean;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeCreate()
     */
    public function beforeCreate(BeanDefinition $beanDefinition)
    {
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterCreate()
     */
    public function afterCreate(&$bean, BeanDefinition $beanDefinition)
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
    public function beforeDefinition($beanName, BeanDefinition &$bean = null)
    {
        $class = $bean->getClass();
        if (empty($class)) {
            return $bean;
        }
        $rClass = ReflectionFactory::getClass($class);
        foreach ($this->_getAnnotations($rClass->getDocComment()) as $annotation) {
            $bean->annotate($annotation);
        }
        foreach ($rClass->getMethods() as $method) {
            $methodName = $method->getName();
            foreach ($this->_getAnnotations($method->getDocComment()) as $annotation) {
                $bean->annotate($annotation, $methodName);
            }
        } 
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeAssemble()
     */
    public function beforeAssemble(&$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterAssemble()
     */
    public function afterAssemble(&$bean, BeanDefinition $beanDefinition)
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
        
    }
}