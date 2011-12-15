<?php
/**
 * This class will test the lifecycle aware feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
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

use Ding\Bean\IBeanDefinitionProvider;
use Ding\Bean\BeanPropertyDefinition;
use Ding\Container\Impl\ContainerImpl;
use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\Lifecycle\IBeforeCreateListener;
use Ding\Bean\Lifecycle\IAfterCreateListener;
use Ding\Bean\Lifecycle\IBeforeAssembleListener;
use Ding\Bean\Lifecycle\IAfterAssembleListener;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Bean\BeanDefinition;
/**
 * This class will test the lifecycle aware feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aware
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Lifecycle_Aware extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array(
                        	'scanDir' => array(realpath(__DIR__))
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_be_lifecycle_aware()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleLifecycleAwareBean');
        $bean = $container->getBean('aSimpleLifecycleAwareBean2');
        $this->assertEquals($bean->something['bc'], 'yeah');
        $this->assertEquals($bean->something['ac'], 'yeah');
        $this->assertEquals($bean->something['aa'], 'yeah');
        $this->assertEquals($bean->something['ad'], 'yeah');
        $this->assertEquals($bean->something['ba'], 'yeah');
    }
}

/**
 * @Configuration
 */
class ClassLifecycleAwareConfiguration
{
    /**
     * @Bean(class=ClassLifecycleAware)
     * @Scope(value=singleton)
     */
    public function aSimpleLifecycleAwareBean()
    {
        return new ClassLifecycleAware();
    }
}

class ClassLifecycleAware2
{
    public $something = array();

    public function setAa($value)
    {
        $this->something['aa'] = $value;
    }
    public function setAc($value)
    {
        $this->something['ac'] = $value;
    }
    public function setBa($value)
    {
        $this->something['ba'] = $value;
    }
    public function setAd($value)
    {
        $this->something['ad'] = $value;
    }
    public function setBc($value)
    {
        $this->something['bc'] = $value;
    }

}

class ClassLifecycleAware implements
    IAfterDefinitionListener, IBeforeCreateListener,
    IAfterCreateListener, IBeforeAssembleListener,
    IAfterAssembleListener, IBeanDefinitionProvider
{
    public function afterAssemble($bean, BeanDefinition $beanDefinition)
    {
        if (get_class($bean) != get_class($this)) {
            $bean->setAa('yeah');
        }
        return $bean;
    }

    public function getBeanDefinition($name)
    {
        if ($name == 'aSimpleLifecycleAwareBean2') {
            $def = new BeanDefinition('aSimpleLifecycleAwareBean2');
            $def->setScope(BeanDefinition::BEAN_SINGLETON);
            $def->setClass('ClassLifecycleAware2');
            return $def;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean.IBeanDefinitionProvider::getByClass()
     */
    public function getBeanDefinitionByClass($class)
    {

    }

    public function afterDefinition(BeanDefinition $bean)
    {
        $props = $bean->getProperties();
        $props[] = new BeanPropertyDefinition('ad', BeanPropertyDefinition::PROPERTY_SIMPLE, 'yeah');
        $bean->setProperties($props);
        return $bean;
    }

    public function beforeAssemble($bean, BeanDefinition $beanDefinition)
    {
        if (get_class($bean) != get_class($this)) {
            $bean->setBa('yeah');
        }
        return $bean;
    }

    public function afterCreate($bean, BeanDefinition $beanDefinition)
    {
        if (get_class($bean) != get_class($this)) {
            $bean->setAc('yeah');
        }
        return $bean;
    }

    public function beforeCreate(BeanDefinition $beanDefinition)
    {
        $props = $beanDefinition->getProperties();
        $props[] = new BeanPropertyDefinition('bc', BeanPropertyDefinition::PROPERTY_SIMPLE, 'yeah');
        $beanDefinition->setProperties($props);
        return $beanDefinition;
    }

    public function __construct()
    {
    }
}
