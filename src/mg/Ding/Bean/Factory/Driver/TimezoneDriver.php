<?php
/**
 * This driver will look up an optional bean called Timezone and if it
 * finds it, will set up the php default timezone.
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

use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Factory\IBeanFactory;

/**
 * This driver will look up an optional bean called Timezone and if it
 * finds it, will set up the php default timezone.
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
class TimezoneDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var TimezoneDriver
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
            $bean = $factory->getBean('Timezone');
            date_default_timezone_set($bean->getTimezone());
        } catch(\Exception $e) {
        }
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return TimezoneDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance !== false) {
            return self::$_instance;
        }
        self::$_instance = new TimezoneDriver;
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
