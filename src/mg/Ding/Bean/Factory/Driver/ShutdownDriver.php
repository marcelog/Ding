<?php
/**
 * This driver will look up an optional bean called ShutdownHandler and if it
 * finds it, will set up a register_shutdown_function().
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
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will look up an optional bean called ShutdownHandler and if it
 * finds it, will set up a register_shutdown_function().
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
class ShutdownDriver
{
    /**
     * Holds current instance.
     * @var ShutdownDriver
     */
    private static $_instance = false;

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

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        try
        {
            $bean = $factory->getBean('ShutdownHandler');
        } catch(\Exception $e) {
            $handler = ReflectionFactory::getClassesByAnnotation('ShutdownHandler');
            if (empty($handler)) {
                return;
            }
            $handler = $handler[0];
            $name = 'ShutdownHandler' . microtime(true);
            $beanDef = new BeanDefinition($name);
            $beanDef->setClass($handler);
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $factory->setBeanDefinition($name, $beanDef);
            $property = new BeanPropertyDefinition('shutdownHandler', BeanPropertyDefinition::PROPERTY_BEAN, $name);
            $beanDef = new BeanDefinition('ShutdownHandler');
            $beanDef->setClass('Ding\\Helpers\\ShutdownHandler\\ShutdownHandlerHelper');
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $beanDef->setProperties(array($property));
            $factory->setBeanDefinition('ShutdownHandler', $beanDef);
            $bean = $factory->getBean('ShutdownHandler');
        }
        register_shutdown_function(array($bean, 'handle'));
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
     * @return ShutdownDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance === false) {
            $ret = new ShutdownDriver($options);
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
