<?php
/**
 * This driver will apply all filters to property values. 
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

use Ding\Bean\BeanPropertyDefinition;

use Ding\Bean\Lifecycle\ILifecycleListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\Filter\PropertyFilter;

/**
 * This driver will apply all filters to property values. 
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
class FiltersDriver implements ILifecycleListener
{
    /**
     * Holds current instance.
     * @var DependsOnDriver
     */
    private static $_instance = false;

    /**
     * Registered filters to apply.
     * @var IFilter[]
     */
    private $_filters;

    /**
     * Recursively, apply filter to property or constructor arguments values.
     *
     * @param BeanPropertyDefinition|BeanConstructoruArgumentDefinition $def
     * 
     * @return void
     */
    private function _applyFilter(&$def)
    {
        $value = $def->getValue();
        if (is_array($value)) {
            foreach ($value as $otherDef) {
                $this->_applyFilter($otherDef);
            }
        } else {
            foreach ($this->_filters as $filter) {
                $def->setValue($filter->apply($value));
            }
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument);
        }
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeConfig()
     */
    public function beforeConfig(IBeanFactory $factory)
    {
        
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        
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
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition &$bean = null)
    {
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
     * @return FiltersDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance === false) {
            $ret = new FiltersDriver($options);
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
        $this->_filters = array(PropertyFilter::getInstance($options));
    }
}