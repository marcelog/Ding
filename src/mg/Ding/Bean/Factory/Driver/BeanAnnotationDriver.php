<?php
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;

class BeanAnnotationDriver implements ILifecycleListener
{
    private static $_instance = false;

    public function beforeDefinition($beanName, BeanDefinition $bean)
    {
        return $bean;
    }
    
    public function afterDefinition($beanName, BeanDefinition $bean)
    {
        return $bean;
    }
    
    public function assemble(&$bean, BeanDefinition &$beanDefinition)
    {
        return $bean;
    }
    
    public function destruct(&$bean, BeanDefinition &$beanDefinition)
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