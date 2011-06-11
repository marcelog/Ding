<?php
/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @version  SVN: $Id$
 * @link     http://marcelog.github.com/
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

use Ding\MessageSource\IMessageSource;
use Ding\Resource\IResourceLoader;
use Ding\Bean\Factory\IBeanFactory;

/**
 * Interface for a container.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Container
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
interface IContainer extends IBeanFactory, IResourceLoader, IMessageSource
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
     * Dispatch an event to all listeners.
     *
     * @param string $eventName The event name.
     * @param mixed  $data      The associated data to the event.
     * @return void
     */
    public function eventDispatch($eventName, $data = null);

    /**
     * Register a new listener to an event. The callback must implement a
     * method named "onEventName($data)".
     *
     * @param string $eventName The event name.
     * @param string $beanName  The event handler.
     *
     * @return void
     */
    public function eventListen($eventName, $beanName);

    /**
     * Returns logger used by the container.
     *
     * @param string $class Will use this parameter to return an appropiate
     * logger.
     *
     * @return \Logger
     */
    public function getLogger($class);
}
