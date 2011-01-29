<?php
/**
 * This driver will take care of all the depends-on beans.
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

use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will take care of all the depends-on beans.
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
class DependsOnDriver implements IAfterDefinitionListener
{
    /**
     * Holds current instance.
     * @var DependsOnDriver
     */
    private static $_instance = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        /**
         * @todo This should be done using a reference to the container (or
         * may be not, but it seems pretty clear we shouldn't be using the
         * factory directly from here).
         */
        foreach ($bean->getDependsOn() as $depBean) {
            $factory->getBean($depBean);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return DependsOnDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance !== false) {
            return self::$_instance;
        }
        self::$_instance = new DependsOnDriver;
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