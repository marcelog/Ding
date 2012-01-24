<?php
/**
 * This driver will take care of the method injection.
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

use Ding\Bean\IBeanDefinitionProvider;
use Ding\Aspect\IAspectManagerAware;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Container\IContainerAware;
use Ding\Container\IContainer;
use Ding\Aspect\PointcutDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectManager;
use Ding\Aspect\MethodInvocation;
use Ding\Aspect\AspectDefinition;
use Ding\Bean\BeanDefinition;

/**
 * An "inner" class. This is the aspect that runs when the method is called.
 * Enter description here ...
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
class MethodInjectionAspect implements IContainerAware
{
    /**
     * Container.
     * @var IContainer
     */
    private $_container;

    /**
     * Bean to generate.
     * @var string
     */
    private $_beanName;

    /**
     * Setter injection for bean name.
     *
     * @param string $beanName Bean name.
     *
     * @return void
     */
    public function setBeanName($beanName)
    {
        $this->_beanName = $beanName;
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
     * Creates a new bean (prototypes).
     *
     * @param MethodInvocation $invocation The call.
     *
     * @return object
     */
    public function invoke(MethodInvocation $invocation)
    {
        return $this->_container->getBean($this->_beanName);
    }
}

/**
 * This driver will take care of the method injection.
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
class MethodInjectionDriver
    implements IAfterDefinitionListener, IAspectManagerAware,
    IContainerAware, IBeanDefinitionProvider
{
    private $_aspectManager;
    private $_beans = array();
    /**
     * Container.
     * @var IContainer
     */
    private $_container;

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
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinition()
     */
    public function getBeanDefinition($name)
    {
        if (isset($this->_beans[$name])) {
            return $this->_beans[$name];
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeanDefinitionByClass()
     */
    public function getBeansByClass($class)
    {
        return array();
    }
    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getBeansListeningOn()
     */
    public function getBeansListeningOn($eventName)
    {
        return array();
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.IAfterDefinitionListener::afterDefinition()
     */
    public function afterDefinition(BeanDefinition $bean)
    {
        foreach ($bean->getMethodInjections() as $method) {
            $aspectBeanName = BeanDefinition::generateName('MethodInjectionAspect');
            $aspectBean = new BeanDefinition($aspectBeanName);
            $aspectBean->setClass('\\Ding\\Bean\\Factory\\Driver\\MethodInjectionAspect');
            $aspectBean->setProperties(array(
                new BeanPropertyDefinition('beanName', BeanPropertyDefinition::PROPERTY_SIMPLE, $method[1])
            ));
            $this->_beans[$aspectBeanName] = $aspectBean;
            $aspectName = BeanDefinition::generateName('MethodInjectionAspect');
            $pointcutName = BeanDefinition::generateName('MethodInjectionPointcut');
            $pointcut = new PointcutDefinition($pointcutName, $method[0], 'invoke');
            $this->_aspectManager->setPointcut($pointcut);
            $aspect = new AspectDefinition(
                $aspectName, array($pointcutName),
                AspectDefinition::ASPECT_METHOD, $aspectBeanName, ''
            );
            $aspects = $bean->getAspects();
            $aspects[] = $aspect;
            $bean->setAspects($aspects);
        }
        return $bean;
    }

    public function setAspectManager(AspectManager $aspectManager)
    {
        $this->_aspectManager = $aspectManager;
    }
}