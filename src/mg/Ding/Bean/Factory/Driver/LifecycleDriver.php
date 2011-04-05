<?php
/**
 * This driver will call lifecycle hooks.
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
use Ding\Bean\Lifecycle\BeanLifecycleManager;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

/**
 * This driver will call lifecycle hooks.
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
class LifecycleDriver implements IAfterAssembleListener
{
    /**
     * Holds current instance.
     * @var LifecycleDriver
     */
    private static $_instance = false;

    public function afterAssemble(IBeanFactory $factory, $bean, BeanDefinition $beanDefinition)
    {
        $class = $beanDefinition->getClass();
        if ($bean === null || empty($class)) {
            return $bean;
        }
        $rClass = ReflectionFactory::getClass($class);
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IBeforeDefinitionListener')) {
            $this->_lifecycleManager->addBeforeDefinitionListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterDefinitionListener')) {
            $this->_lifecycleManager->addAfterDefinitionListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IBeforeCreateListener')) {
            $this->_lifecycleManager->addBeforeCreateListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterCreateListener')) {
            $this->_lifecycleManager->addAfterCreateListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IBeforeAssembleListener')) {
            $this->_lifecycleManager->addBeforeAssembleListener($bean);
        }
        if ($rClass->implementsInterface('Ding\Bean\Lifecycle\IAfterAssembleListener')) {
            $this->_lifecycleManager->addAfterAssembleListener($bean);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return LifecycleDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new LifecycleDriver;
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
        $this->_lifecycleManager = BeanLifecycleManager::getInstance();
    }
}