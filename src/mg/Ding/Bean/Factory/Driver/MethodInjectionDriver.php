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
use Ding\Container\IContainerAware;
use Ding\Container\IContainer;
use Ding\Aspect\PointcutDefinition;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Aspect\AspectManager;
use Ding\Aspect\MethodInvocation;
use Ding\Aspect\AspectDefinition;
use Ding\Bean\Lifecycle\IBeforeDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;

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
     * Factory to use.
     * @var IBeanFactory
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
class MethodInjectionDriver implements IBeforeDefinitionListener
{
    /**
     * Holds current instance.
     * @var MethodInjectionDriver
     */
    private static $_instance = false;

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::beforeDefinition()
     */
    public function beforeDefinition(IBeanFactory $factory, $beanName, BeanDefinition $bean = null)
    {
        if ($bean === null) {
            return $bean;
        }
        foreach ($bean->getMethodInjections() as $method) {
            $aspectBeanName = 'MethodInjectionAspect' . rand(1, microtime(true));
            $aspectBean = new BeanDefinition($aspectBeanName);
            $aspectBean->setScope(BeanDefinition::BEAN_SINGLETON);
            $aspectBean->setClass('\\Ding\\Bean\\Factory\\Driver\\MethodInjectionAspect');
            $aspectBean->setProperties(array(
                new BeanPropertyDefinition('beanName', BeanPropertyDefinition::PROPERTY_SIMPLE, $method[1])
            ));
            $factory->setBeanDefinition($aspectBeanName, $aspectBean);
            $aspectName = 'MethodInjectionAspect' . rand(1, microtime(true));
            $pointcutName = 'MethodInjectionPointcut' . rand(1, microtime(true));
            $pointcut = new PointcutDefinition($pointcutName, $method[0], 'invoke');
            $this->_aspectManager->setPointcut($pointcut);
            $aspect = new AspectDefinition(
                $aspectName, array($pointcutName),
                AspectDefinition::ASPECT_METHOD, $aspectBeanName, ''
            );
            //$this->_aspectManager->setAspect($aspect);
            $aspects = $bean->getAspects();
            $aspects[] = $aspect;
            $bean->setAspects($aspects);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return MethodInjectionDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new MethodInjectionDriver;
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
        $this->_aspectManager = AspectManager::getInstance();
    }
}