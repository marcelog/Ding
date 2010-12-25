<?php
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Reflection\ReflectionFactory;

class BeanAnnotationDriver implements ILifecycleListener
{
    private static $_instance = false;

    public function afterDefinition($beanName, BeanDefinition $bean)
    {
        return $bean;
    }
    
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
        
    public function beforeDefinition($beanName, BeanDefinition $bean)
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
    
    public function beforeAssemble($bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    public function afterAssemble($bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }
    
    public function destruct($bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }
    
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
    
    private function __construct(array $options)
    {
        
    }
}