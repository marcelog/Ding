<?php
/**
 * This driver will lookup a bean definition in apc.
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
use Ding\Bean\Factory\BeanFactory;
use Ding\Reflection\ReflectionFactory;
use Ding\Cache\CacheLocator;
use Ding\Cache\ICache;

/**
 * This driver will lookup a bean definition in apc.
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
class BeanCacheDefinitionDriver implements ILifecycleListener
{
    /**
     * Holds current instance.
     * @var BeanAnnotationDriver
     */
    private static $_instance = false;

    /**
     * References cache.
     * @var ICache
     */
    private $_cache;
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(BeanFactory $factory, BeanDefinition &$bean)
    {
        return $bean;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeCreate()
     */
    public function beforeCreate(BeanFactory $factory, BeanDefinition $beanDefinition)
    {
        $beanName = $beanDefinition->getName() . '.beandef';
        if (!$this->_cache->has($beanName)) {
            $this->_cache->store($beanName, $beanDefinition);
        }
        return $beanDefinition;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterCreate()
     */
    public function afterCreate(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }
    
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     */
    public function beforeDefinition(BeanFactory $factory, $beanName, BeanDefinition &$bean = null)
    {
        if ($bean != null) {
            return $bean;
        }
        $result = false;
        $beanDef = $this->_cache->fetch($beanName . '.beandef', $result);
        if ($result === false) {
            return $bean;
        }
        return $beanDef;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeAssemble()
     */
    public function beforeAssemble(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
    {
        return $bean;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterAssemble()
     */
    public function afterAssemble(BeanFactory $factory, &$bean, BeanDefinition $beanDefinition)
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
     * @return BeanAPCDefinitionDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance === false) {
            $ret = new BeanCacheDefinitionDriver($options);
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
        $this->_cache = CacheLocator::getDefinitionsCacheInstance();
    }
}