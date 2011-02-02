<?php
/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://www.noneyet.ar/
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
namespace Ding\Container;

use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\Lifecycle\IBeforeConfigListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\Lifecycle\IBeforeDestructListener;
use Ding\Bean\Lifecycle\BeanLifecycle;

/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
interface IContainer extends IBeanFactory
{
    /**
     * Register a shutdown (destroy-method) method for a bean.
     *
     * @param object $bean   Bean to call.
     * @param string $method Method to call.
     *
     * @see Ding\Container.IContainer::registerShutdownMethod()
     *
     * @return void
     */
    public function registerShutdownMethod($bean, $method);

    /**
     * Register a listener for BeforeConfig.
     *
     * @param IBeforeConfigListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeConfigListener(IBeforeConfigListener $listener);

    /**
     * Register a listener for AfterConfig.
     *
     * @param IAfterConfigListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterConfigListener(IAfterConfigListener $listener);

    /**
     * Register a listener for BeforeDefinition.
     *
     * @param IBeforeDefinitionListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeDefinitionListener(IBeforeDefinitionListener $listener);

    /**
     * Register a listener for AfterDefinition.
     *
     * @param IAfterDefinitionListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterDefinitionListener(IAfterDefinitionListener $listener);

    /**
     * Register a listener for BeforeCreate.
     *
     * @param IBeforeCreateListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeCreateListener(IBeforeCreateListener $listener);

    /**
     * Register a listener for AfterCreate.
     *
     * @param IAfterCreateListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterCreateListener(IAfterCreateListener $listener);

    /**
     * Register a listener for BeforeAssemble.
     *
     * @param IBeforeAssembleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeAssembleListener(IBeforeAssembleListener $listener);

    /**
     * Register a listener for AfterAssemble.
     *
     * @param IAfterAssembleListener $listener Listener to be called.
     *
     * @return void
     */
    public function addAfterAssembleListener(IAfterAssembleListener $listener);

    /**
     * Register a listener for BeforeDestruct.
     *
     * @param IBeforeDestructListener $listener Listener to be called.
     *
     * @return void
     */
    public function addBeforeDestructListener(IBeforeDestructListener $listener);
}
