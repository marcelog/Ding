<?php
/**
 * This driver will inject the MessageSource to the beans that implement
 * IMessageSourceAware
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

use Ding\Reflection\ReflectionFactory;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Factory\IBeanFactory;

/**
 * This driver will inject the MessageSource to the beans that implement
 * IMessageSourceAware
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
class MessageSourceDriver implements IAfterConfigListener, IAfterCreateListener
{
    /**
     * Holds current instance.
     * @var MessageSourceDriver
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
            $bean = $factory->getBean('messageSource');
            $factory->setMessageSource($bean);
        } catch(\Exception $e) {
        }
    }

    public function afterCreate(IBeanFactory $factory, $bean, BeanDefinition $beanDefinition)
    {
        $class = $beanDefinition->getClass();
        if ($class === false || empty($class)) {
            return $bean;
        }
        $rClass = ReflectionFactory::getClass($class);
        if ($rClass->implementsInterface('Ding\MessageSource\IMessageSourceAware')) {
            $bean->setMessageSource($factory);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return MessageSourceDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new MessageSourceDriver;
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
