<?php
/**
 * This driver will look up an optional bean called SignalHandler and if it
 * finds it, will set it up to be the signal handler.
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

use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will look up an optional bean called SignalHandler and if it
 * finds it, will set it up to be the signal handler.
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
class SignalHandlerDriver implements IAfterConfigListener
{
    /**
     * Holds current instance.
     * @var SignalHandlerDriver
     */
    private static $_instance = false;

    /**
     * Signals to handle.
     * @var array
     */
    private static $_signals = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig(IBeanFactory $factory)
    {
        try
        {
            $bean = $factory->getBean('SignalHandler');
        } catch(\Exception $e) {
            $handler = ReflectionFactory::getClassesByAnnotation('SignalHandler');
            if (count($handler) == 0) {
                return;
            }
            $handler = array_pop($handler);
            $name = 'SignalHandler' . microtime(true);
            $beanDef = new BeanDefinition($name);
            $beanDef->setClass($handler);
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $factory->setBeanDefinition($name, $beanDef);
            $property = new BeanPropertyDefinition('signalHandler', BeanPropertyDefinition::PROPERTY_BEAN, $name);
            $beanDef = new BeanDefinition('SignalHandler');
            $beanDef->setClass('Ding\\Helpers\\SignalHandler\\SignalHandlerHelper');
            $beanDef->setScope(BeanDefinition::BEAN_SINGLETON);
            $beanDef->setProperties(array($property));
            $factory->setBeanDefinition('SignalHandler', $beanDef);
            $bean = $factory->getBean('SignalHandler');
        }
        $handler = array($bean, 'handle');
        foreach (self::$_signals as $signal) {
            pcntl_signal($signal, $handler);
        }
        pcntl_sigprocmask(SIG_UNBLOCK, self::$_signals);
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return SignalHandlerDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new SignalHandlerDriver;
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
        self::$_signals = array(
            SIGQUIT, SIGHUP, SIGINT, SIGCHLD, SIGTERM, SIGUSR1, SIGUSR2
        );
    }
}
