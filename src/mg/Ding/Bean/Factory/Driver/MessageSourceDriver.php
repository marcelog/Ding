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

use Ding\Reflection\IReflectionFactoryAware;
use Ding\Reflection\IReflectionFactory;
use Ding\Bean\BeanDefinition;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IAfterConfigListener;
use Ding\Container\IContainerAware;
use Ding\Container\IContainer;

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
class MessageSourceDriver
    implements IAfterConfigListener, IAfterCreateListener,
    IContainerAware, IReflectionFactoryAware
{
    /**
     * Container.
     * @var IContainer
     */
    private $_container;
    /**
     * A ReflectionFactory implementation.
     * @var IReflectionFactory
     */
    protected $reflectionFactory;

    /**
     * (non-PHPdoc)
     * @see Ding\Reflection.IReflectionFactoryAware::setReflectionFactory()
     */
    public function setReflectionFactory(IReflectionFactory $reflectionFactory)
    {
        $this->reflectionFactory = $reflectionFactory;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Container.IContainerAware::setContainer()
     */
    public function setContainer(IContainer $container)
    {
        $this->_container = $container;
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterConfig()
     */
    public function afterConfig()
    {
        try
        {
            $bean = $this->_container->getBean('messageSource');
            $this->_container->setMessageSource($bean);
        } catch(\Exception $e) {
        }
    }

    public function afterCreate($bean, BeanDefinition $beanDefinition)
    {
        $rClass = $this->reflectionFactory->getClass(get_class($bean));
        if ($rClass->implementsInterface('Ding\MessageSource\IMessageSourceAware')) {
            $bean->setMessageSource($this->_container);
        }
        return $bean;
    }
}
