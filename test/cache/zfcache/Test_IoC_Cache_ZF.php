<?php
/**
 * This class will test the ioc with zf cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Zfcache
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

use Ding\Cache\Locator\CacheLocator;
use Ding\Container\Impl\ContainerImpl;
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This class will test the ioc with zf cache.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Zfcache
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_IoC_Cache_ZF extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $cachedir = implode(DIRECTORY_SEPARATOR, array(getenv('TMPDIR'), 'cache', __CLASS__));
        @mkdir($cachedir, 0755, true);
        $zendCacheOptions = array(
            'frontend' => 'Core',
            'backend' => 'File',
            'backendoptions' => array('cache_dir' => $cachedir),
            'frontendoptions' => array('lifetime' => 10000, 'automatic_serialization' => true)
        );
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'cache' => array(
    				'proxy' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
                	'aspect' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
                    'autoloader' => array('impl' => 'dummy', 'zend' => $zendCacheOptions),
        			'annotations' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
            		'bdef' => array('impl' => 'zend', 'zend' => $zendCacheOptions),
              		'beans' => array('impl' => 'dummy')
                ),
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'ioc-xml-simple.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    public function tearDown()
    {
        $cache = CacheLocator::getProxyCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getDefinitionsCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getAnnotationsCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getAutoloaderCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getBeansCacheInstance();
        $cache->flush();
        $cache = CacheLocator::getAspectCacheInstance();
        $cache->flush();
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
    public function can_constructor_args()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleConstArgBean');
        $this->assertEquals($bean->value, '1a$');
        $this->assertTrue($bean->true);
        $this->assertFalse($bean->false);
        $this->assertNull($bean->null);
        $this->assertEquals($bean->eval, 'evaled code');
        $this->assertTrue($bean->ref instanceof ClassSimpleXML);
        $this->assertTrue($bean->innerBean instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleXML);
        $this->assertTrue($bean->array['key7'] instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array['key8']));
    }


    /**
     * @test
     */
    public function can_setter_args()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSetArgBean');
        $this->assertEquals($bean->value, '1a$');
        $this->assertTrue($bean->true);
        $this->assertFalse($bean->false);
        $this->assertNull($bean->null);
        $this->assertEquals($bean->eval, 'evaled code');
        $this->assertTrue($bean->ref instanceof ClassSimpleXML);
        $this->assertTrue($bean->innerBean instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleXML);
        $this->assertTrue($bean->array['key7'] instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array['key8']));
    }

    /**
     * @test
     */
    public function can_setter_args_cached()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSetArgBean');
        $this->assertEquals($bean->value, '1a$');
        $this->assertTrue($bean->true);
        $this->assertFalse($bean->false);
        $this->assertNull($bean->null);
        $this->assertEquals($bean->eval, 'evaled code');
        $this->assertTrue($bean->ref instanceof ClassSimpleXML);
        $this->assertTrue($bean->innerBean instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleXML);
        $this->assertTrue($bean->array['key7'] instanceof ClassSimpleXML);
        $this->assertTrue(is_array($bean->array['key8']));
    }

    /**
     * @test
     */
    public function can_singleton()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSingletonBean');
        $bean2 = $container->getBean('aSimpleSingletonBean');

        $this->assertTrue($bean instanceof ClassSimpleXML);
        $this->assertTrue($bean2 instanceof ClassSimpleXML);

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

        $this->assertTrue($bean instanceof ClassSimpleXML);
        $this->assertTrue($bean2 instanceof ClassSimpleXML);

        // Contrary to what happens with singletons, in this case both "sets"
        // will go to different instances.
        $bean->setSomething(rand(1, microtime(true)));
        $bean2->setSomething(rand(1, microtime(true)));
        $this->assertFalse($bean->getSomething() === $bean2->getSomething());
    }

    /**
     * @test
     */
    public function can_import()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('importedBean');
        $this->assertTrue($bean instanceof ClassSimpleXML5);
        $this->assertTrue($bean->dependency instanceof ClassSimpleXML);
    }

    /**
     * @test
     */
    public function can_depends_on()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleDependsOn');
        $this->assertTrue($bean instanceof ClassSimpleXML6);
        $this->assertTrue(ClassSimpleXML7::$value);
        $this->assertTrue(ClassSimpleXML8::$value);
    }

    /**
     * @test
     */
    public function can_method_lookup()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleMethodLookupBean');
        $this->assertTrue($bean instanceof ClassSimpleXML10);
        $this->assertTrue($bean->createAnotherBean() instanceof ClassSimpleXML);
    }

    /**
     * @test
     */
    public function can_multiple_dirs()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => array('ioc-xml-simple.xml', 'moreBeans.xml'),
                        	'directories' => array(RESOURCES_DIR, RESOURCES_DIR . DIRECTORY_SEPARATOR . 'moreBeans')
                        )
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('aSecretBean');
        $this->assertTrue($bean instanceof ClassSimpleXML9);
        $this->assertTrue($bean->value instanceof ClassSimpleXML);
    }

    /**
     * @test
     */
    public function can_factory_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleBeanFactory');
        $this->assertTrue($bean instanceof ClassSimpleXML11);
        $this->assertEquals($bean->a, 'value1');
        $this->assertEquals($bean->b, 'value2');
        $this->assertEquals($bean->c, 'value3');
    }

    /**
     * @test
     */
    public function can_factory_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleBeanFactoryFromOtherBean');
        $this->assertTrue($bean instanceof ClassSimpleXML11);
        $this->assertEquals($bean->a, 'value1');
        $this->assertEquals($bean->b, 'value2');
        $this->assertEquals($bean->c, 'value3');
    }

    /**
     * @test
     */
    public function can_cache_property_name_for_setter_driver()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSetArgBean');
        $bean2 = $container->getBean('aSimpleSetArgBean2');

        $this->assertTrue($bean instanceof ClassSimpleXML3);
        $this->assertTrue($bean2 instanceof ClassSimpleXML12);
    }
}
