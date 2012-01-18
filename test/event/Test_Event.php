<?php
/**
 * This class will test the events feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Events
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

use Ding\Container\Impl\ContainerImpl;

/**
 * This class will test the events feature.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Events
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Event extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(),
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'events.xml', 'directories' => array(RESOURCES_DIR)
                        ),
                        'annotation' => array('scanDir' => array(__DIR__)),
                        'yaml' => array(
                        	'filename' => 'events.yaml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_trigger_events()
    {
        $container = \Ding\Container\Impl\ContainerImpl::getInstance($this->_properties);
        $container->eventListen('beanCreated', 'aBeanPragmaticallyListeningForEvents');
        $bean = $container->getBean('eventBean2');
        $this->assertTrue(EventBean::$eventDetected);
        $this->assertTrue(EventBean3::$eventDetected);
        $this->assertTrue(EventBean4::$eventDetected);
        $this->assertTrue(EventBean5::$eventDetected);
        $this->assertTrue(ParentThatListensForEvent::$eventDetected);
    }
}

class EventBean2 implements \Ding\Container\IContainerAware
{
    private $_container;
    public function setContainer(\Ding\Container\IContainer $container)
    {
        $this->_container = $container;
    }

    public function initMethod()
    {
        $this->_container->eventDispatch('beanCreated', $this);
    }
}

class EventBean4
{
    public static $eventDetected = false;
    public function onBeanCreated($data = null)
    {
        self::$eventDetected = true;
    }
}
class EventBean5
{
    public static $eventDetected = false;
    public function onBeanCreated($data = null)
    {
        self::$eventDetected = true;
    }
}
class EventBean
{
    public static $eventDetected = false;
    public function onBeanCreated($data = null)
    {
        self::$eventDetected = true;
    }
}
class EventBean3
{
    public static $eventDetected = false;
    public function onBeanCreated($data = null)
    {
        self::$eventDetected = true;
    }
}

/**
 * @Configuration
 */
class EventBeanConfig
{
    /**
     * @Bean(class=EventBean4)
     * @Scope(value=singleton)
     * @ListensOn(value=beanCreated)
     */
    public function eventBean4()
    {
        return new EventBean4;
    }
}

/**
 * @Component
 * @ListensOn(value="beanCreated")
 */
abstract class ParentThatListensForEvent
{
    public static $eventDetected = false;

    public function onBeanCreated($data)
    {
        self::$eventDetected = true;
    }
}

/**
 * @Component(name="aChildBeanThatListensForEvents")
 * @ListensOn(value="beanCreated")
 */
class AChildThatListensForEvent extends ParentThatListensForEvent
{
}
