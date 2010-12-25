<?php
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Reflection\ReflectionFactory;

class BeanAnnotationDriver implements ILifecycleListener
{
    private static $_instance = false;

    public function beforeDefinition($beanName, BeanDefinition $bean)
    {
        return $bean;
    }
    
    public function afterDefinition($beanName, BeanDefinition $bean)
    {
        $class = $bean->getClass();
        $rClass = ReflectionFactory::getClass($class);
        foreach ($rClass->getMethods() as $method) {
            $doc = $method->getDocComment();
            if (preg_match_all('/@.+/', $doc, $matches) > 0) {
                $argsStart = strpos($matches[0][0], '(');
                $arguments = array();
                if ($argsStart != false) {
                    $name = substr($matches[0][0], 1, $argsStart - 1);
                    $args = substr($matches[0][0], $argsStart + 1, -1);
                    /*
                     * http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs
                     */
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
                    $name = substr($matches[0][0], 1);
                }
                $definition = new BeanAnnotationDefinition($name, $arguments);
                $bean->annotate($definition);
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