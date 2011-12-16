<?php
/**
 * The lifecycle manager for the container.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Lifecycle
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
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
namespace Ding\Bean\Lifecycle;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\BeanDefinition;

/**
 * The lifecycle manager for the container.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Lifecycle
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class BeanLifecycleManager
{
    /**
     * Lifecycle handlers for beans.
     * @var ILifecycleListener
     */
    private $_lifecyclers;

    /**
     * Serialization
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_lifecyclers');
    }

    /**
     * Adds a lifecycler to the AfterConfig point.
     *
     * @param IAfterConfigListener $listener Listener to add
     *
     * @return void
     */
    public function addAfterConfigListener(IAfterConfigListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterConfig][] = $listener;
    }

    /**
     * Adds a lifecycler to the AfterDefinition point.
     *
     * @param IAfterDefinitionListener $listener Listener to add
     *
     * @return void
     */
    public function addAfterDefinitionListener(IAfterDefinitionListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterDefinition][] = $listener;
    }

    /**
     * Adds a lifecycler to the BeforeCreate point.
     *
     * @param IBeforeCreateListener $listener Listener to add
     *
     * @return void
     */
    public function addBeforeCreateListener(IBeforeCreateListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeCreate][] = $listener;
    }

    /**
     * Adds a lifecycler to the AfterCreate point.
     *
     * @param IAfterCreateListener $listener Listener to add
     *
     * @return void
     */
    public function addAfterCreateListener(IAfterCreateListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterCreate][] = $listener;
    }

    /**
     * Adds a lifecycler to the BeforeAssemble point.
     *
     * @param IBeforeAssembleListener $listener Listener to add
     *
     * @return void
     */
    public function addBeforeAssembleListener(IBeforeAssembleListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::BeforeAssemble][] = $listener;
    }

    /**
     * Adds a lifecycler to the AfterAssemble point.
     *
     * @param IAfterAssembleListener $listener Listener to add
     *
     * @return void
     */
    public function addAfterAssembleListener(IAfterAssembleListener $listener)
    {
        $this->_lifecyclers[BeanLifecycle::AfterAssemble][] = $listener;
    }

    /**
     * Runs the AfterDefinition point of the lifecycle.
     *
     * @param string         $beanName Bean name.
     * @param BeanDefinition $bean     Actual definition.
     *
     * @return BeanDefinition
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        $return = $bean;
        foreach ($this->_lifecyclers[BeanLifecycle::AfterDefinition] as $lifecycleListener) {
            $return = $lifecycleListener->afterDefinition($return);
        }
        return $return;
    }

    /**
     * Runs the BeforeCreate point of the lifecycle.
     *
     * @param BeanDefinition $beanDefinition Actual definition.
     *
     * @return void
     */
    public function beforeCreate(BeanDefinition $beanDefinition)
    {
        foreach ($this->_lifecyclers[BeanLifecycle::BeforeCreate] as $lifecycleListener) {
            $lifecycleListener->beforeCreate($beanDefinition);
        }
    }

    /**
     * Runs the AfterCreate point of the lifecycle.
     *
     * @param string         $beanName Bean name.
     * @param BeanDefinition $bean     Actual definition.
     *
     * @return void
     */
    public function afterCreate($bean, BeanDefinition $beanDefinition)
    {
        foreach ($this->_lifecyclers[BeanLifecycle::AfterCreate] as $lifecycleListener) {
            $lifecycleListener->afterCreate($bean, $beanDefinition);
        }
    }

    /**
     * Runs the BeforeAssemble point of the lifecycle.
     *
     * @param string         $beanName Bean name.
     * @param BeanDefinition $bean     Actual definition.
     *
     * @return BeanDefinition
     */
    public function beforeAssemble($bean, BeanDefinition $beanDefinition)
    {
        foreach ($this->_lifecyclers[BeanLifecycle::BeforeAssemble] as $lifecycleListener) {
            $lifecycleListener->beforeAssemble($bean, $beanDefinition);
        }
    }

    /**
     * Runs the AfterAssemble point of the lifecycle.
     *
     * @param object         $bean           Bean.
     * @param BeanDefinition $beanDefinition Actual definition.
     *
     * @return void
     */
    public function afterAssemble($bean, BeanDefinition $beanDefinition)
    {
        foreach ($this->_lifecyclers[BeanLifecycle::AfterAssemble] as $lifecycleListener) {
            $lifecycleListener->afterAssemble($bean, $beanDefinition);
        }
    }

    /**
     * Runs the AfterConfig point of the lifecycle.
     *
     * @return void
     */
    public function afterConfig()
    {
        foreach ($this->_lifecyclers[BeanLifecycle::AfterConfig] as $lifecycleListener) {
            $lifecycleListener->afterConfig();
        }
    }

/*
    public function setListeners(array $listeners = array())
    {
        $this->_lifecyclers[BeanLifecycle::BeforeConfig] = $listeners[BeanLifecycle::BeforeConfig];
        $this->_lifecyclers[BeanLifecycle::AfterConfig] = $listeners[BeanLifecycle::AfterConfig];
        $this->_lifecyclers[BeanLifecycle::AfterDefinition] = $listeners[BeanLifecycle::AfterDefinition];
        $this->_lifecyclers[BeanLifecycle::BeforeCreate] = $listeners[BeanLifecycle::BeforeCreate];
        $this->_lifecyclers[BeanLifecycle::AfterCreate] = $listeners[BeanLifecycle::AfterCreate];
        $this->_lifecyclers[BeanLifecycle::BeforeAssemble] = $listeners[BeanLifecycle::BeforeAssemble];
        $this->_lifecyclers[BeanLifecycle::AfterAssemble] = $listeners[BeanLifecycle::AfterAssemble];
    }
*/
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $soullessArray = array();
        $this->_lifecyclers = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterConfig] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterDefinition] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeCreate] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterCreate] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::BeforeAssemble] = $soullessArray;
        $this->_lifecyclers[BeanLifecycle::AfterAssemble] = $soullessArray;
    }
}
