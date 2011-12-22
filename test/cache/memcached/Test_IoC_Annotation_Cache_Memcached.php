<?php
/**
 * This class will test the annotation driver with memcached.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Memcached
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
 * This class will test the annotation driver with memcached.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Memcached
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_IoC_Annotation_Cache_Memcached extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $memcachedOptions = array('host' => '127.0.0.1', 'port' => 11211);
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(
    				'proxy' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
                	'aspect' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
                    'autoloader' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
            		'bdef' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
        			'annotations' => array('impl' => 'memcached', 'memcached' => $memcachedOptions),
              		'beans' => array('impl' => 'dummy')
                ),
        		'factory' => array(
                    'bdef' => array(
                        'annotation' => array(
                        	'scanDir' => array(realpath(__DIR__ . '/../../ioc/annotation'))
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_get_from_a_namespaced_class()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBeanFromANamespacedClass');
        $this->assertTrue($bean instanceof Some\Namespaces\Clazz\SomeOtherNamespacedClass);
    }
    /**
     * @test
     */
    public function can_singleton()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSingletonBean');
        $bean2 = $container->getBean('aSimpleSingletonBean');

        $this->assertTrue($bean instanceof ClassSimpleAnnotation);
        $this->assertTrue($bean2 instanceof ClassSimpleAnnotation);

        // If this is truly a singleton, both "sets" will go to the same
        // object reference.
        $bean->setSomething(rand(1, microtime(true)));
        $bean2->setSomething(rand(1, microtime(true)));
        $this->assertTrue($bean->getSomething() === $bean2->getSomething());
    }

    /**
     * @test
     */
    public function can_prototype()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimplePrototypeBean');
        $bean2 = $container->getBean('aSimplePrototypeBean');

        $this->assertTrue($bean instanceof ClassSimpleAnnotation);
        $this->assertTrue($bean2 instanceof ClassSimpleAnnotation);

        // Contrary to what happens with singletons, in this case both "sets"
        // will go to different instances.
        $bean->setSomething(rand(1, microtime(true)));
        $bean2->setSomething(rand(1, microtime(true)));
        $this->assertFalse($bean->getSomething() === $bean2->getSomething());
    }

    /**
     * @test
     */
    public function can_rename()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('renamedBean');
        $this->assertTrue($bean instanceof ClassSimpleAnnotation);
    }

    /**
     * @test
     */
    public function can_init_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleInitMethodBean');
        $this->assertTrue($bean->something);
    }

    /**
     * @test
     */
    public function can_destroy_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleDestroyMethodBean');
         // XXX bad... unset() does not work because ContainerImpl is a singleton
         // and holds a reference to itself, so the destructor is never called.
        $container->__destruct();
        $this->assertNull($bean->something);
    }

    /**
     * @test
     */
    public function can_at_resource()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('injectedBean');
        $this->assertTrue($bean->aSimplePrototypeBean instanceof ClassSimpleAnnotation);
        $this->assertTrue($bean->getASimpleSingletonBean() instanceof ClassSimpleAnnotation);
        $this->assertTrue($bean->somethingElse instanceof ASimpleDestroyInitClass);
    }

    /**
     * @test
     */
    public function can_at_required()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('required');
        $this->assertTrue($bean->value1 instanceof ClassSimpleAnnotation2);
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_at_required_missing_property()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('requiredFails');
    }
    /**
     * @test
     */
    public function can_class_init_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleInitMethodClass');
        $this->assertTrue($bean->something);
    }

    /**
     * @test
     */
    public function can_class_destroy_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleDestroyMethodClass');
         // XXX bad... unset() does not work because ContainerImpl is a singleton
         // and holds a reference to itself, so the destructor is never called.
        $container->__destruct();
        $this->assertNull($bean->something);
    }
}
