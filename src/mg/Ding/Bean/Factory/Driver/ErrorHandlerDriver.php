<?php
/**
 * This driver will look up an optional bean called ErrorHandler and if it
 * finds it, will set it up to be the error handler.
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
use Ding\Bean\BeanDefinition;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Factory\IBeanFactory;

/**
 * This driver will look up an optional bean called ErrorHandler and if it
 * finds it, will set it up to be the error handler.
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
class ErrorHandlerDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var ErrorHandlerDriver
     */
    private static $_instance = false;
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        try
        {
            $bean = $factory->getBean('ErrorHandler');
        } catch(\Exception $e) {
            $handler = ReflectionFactory::getClassesByAnnotation('ErrorHandler');
            if (count($handler) == 0) {
                return;
            }
            $handler = array_pop($handler);
            $name = 'ErrorHandler' . microtime(true);
            $beanDef = new BeanDefinition($name);
            $beanDef->setClass($handler);
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $factory->setBeanDefinition($name, $beanDef);
            $property = new BeanPropertyDefinition('errorHandler', BeanPropertyDefinition::PROPERTY_BEAN, $name);
            $beanDef = new BeanDefinition('ErrorHandler');
            $beanDef->setClass('Ding\\Helpers\\ErrorHandler\\ErrorHandlerHelper');
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $beanDef->setProperties(array($property));
            $factory->setBeanDefinition('ErrorHandler', $beanDef);
            $bean = $factory->getBean('ErrorHandler');
        }
        set_error_handler(array($bean, 'handle'));
    }
    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return ErrorHandlerDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance !== false) {
            return self::$_instance;
        }
        self::$_instance = new ErrorHandlerDriver;
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct()
    {
    }
}