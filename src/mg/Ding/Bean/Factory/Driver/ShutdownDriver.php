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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\Lifecycle\IAfterConfigListener;
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class ShutdownDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var ShutdownDriver
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
            $bean = $factory->getBean('ShutdownHandler');
        } catch(\Exception $e) {
            $handler = ReflectionFactory::getClassesByAnnotation('ShutdownHandler');
            if (count($handler) == 0) {
                return;
            }
            $handler = array_pop($handler);
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
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return ShutdownDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new ShutdownDriver;
        }
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
