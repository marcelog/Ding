<?php
/**
 * This class will test the XML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Xml
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
use Ding\Bean\Factory\Exception\BeanFactoryException;

/**
 * This class will test the XML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Xml
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_XML_IoC extends PHPUnit_Framework_TestCase
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
                        	'filename' => 'ioc-xml-simple.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_read_beans_file()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'ioc-xml-inexistant.xml', 'directories' => array('neverland')
                        ),
                        'annotation' => array(
                        	'scanDir' => array(realpath(__DIR__))
                        )
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('aSimpleInitMethodBean');
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_parse_beans_file()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'ioc-xml-invalid.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('aSimpleInitMethodBean');
    }


    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_unknown_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $this->assertFalse($container->getBean('unknownBean'));
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_invalid_scope()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $container->getBean('invalidScopeBean');
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
        $bean = $container->getBean('aSimpleBeanFactoryNoArgs');
        $this->assertTrue($bean instanceof ClassSimpleXML11);
        $this->assertEquals($bean->a, 1);
        $this->assertEquals($bean->b, 2);
        $this->assertEquals($bean->c, 3);
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
        $bean = $container->getBean('aSimpleBeanFactoryFromOtherBeanNoArgs');
        $this->assertTrue($bean instanceof ClassSimpleXML11);
        $this->assertEquals($bean->a, 1);
        $this->assertEquals($bean->b, 2);
        $this->assertEquals($bean->c, 3);
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

    /**
     * @test
     */
    public function can_inherit_from_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('childBean');
        $this->assertEquals($bean->someProperty, 'inheritedValue');
    }

    /**
     * @test
     */
    public function can_get_globally_aliased_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('globalAliasedBean');
        $this->assertTrue($bean instanceof ClassSimpleXML2);
    }

    /**
     * @test
     */
    public function can_get_aliased_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aliasedBean');
        $this->assertTrue($bean instanceof ClassSimpleXML);
    }

    /**
     * @test
     */
    public function can_get_by_class()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBeanDefinitionByClass('aliasedBean');
    }
}

class ClassSimpleXML
{
    private $_something;

    public function getSomething()
    {
        return $this->_something;
    }

    public function setSomething($v)
    {
        $this->_something = $v;
    }

    public function __construct()
    {
    }
}

class ClassSimpleXML2
{
    public $value = 'not set';
    public $true = 'not set';
    public $false = 'not set';
    public $null = 'not set';
    public $eval = 'not set';
    public $ref = 'not set';
    public $innerBean = 'not set';
    public $array = 'not set';

    public function __construct($value, $true, $false, $null, $eval, $ref, $innerBean, $array)
    {
        $this->value = $value;
        $this->true = $true;
        $this->false = $false;
        $this->null = $null;
        $this->eval = $eval;
        $this->ref = $ref;
        $this->innerBean = $innerBean;
        $this->array = $array;
    }
}

class ClassSimpleXML3
{
    public $value = 'not set';
    public $true = 'not set';
    public $false = 'not set';
    public $null = 'not set';
    public $eval = 'not set';
    public $ref = 'not set';
    public $innerBean = 'not set';
    public $array = 'not set';

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setTrue($value)
    {
        $this->true = $value;
    }

    public function setFalse($value)
    {
        $this->false = $value;
    }

    public function setNull($value)
    {
        $this->null = $value;
    }

    public function setEvaledCode($value)
    {
        $this->eval = $value;
    }

    public function setRef($value)
    {
        $this->ref = $value;
    }

    public function setInnerBean($value)
    {
        $this->innerBean = $value;
    }

    public function setArray($value)
    {
        $this->array = $value;
    }
}

class ClassSimpleXML4
{
    public $something = false;

    public function initMethod()
    {
        $this->something = true;
    }

    public function destroyMethod()
    {
        $this->something = null;
    }

    public function __construct()
    {
    }
}

class ClassSimpleXML5
{
    public $dependency;

    public function __construct($value)
    {
        $this->dependency = $value;
    }
}

class ClassSimpleXML6
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleXML7
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleXML8
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleXML9
{
    public $value = null;

    public function setSecretBean($value)
    {
        $this->value = $value;
    }

    public function __construct()
    {
    }
}

class ClassSimpleXML10
{
    public function createAnotherBean()
    {

    }

    public function __construct()
    {
    }
}

class ClassSimpleXML11
{
    public $a;
    public $b;
    public $c;

    public static function getInstanceNoArgs()
    {
        return new ClassSimpleXML11(1, 2, 3);
    }

    public function factoryMethodNoArgs()
    {
        return new ClassSimpleXML11(1, 2, 3);
    }

    public static function getInstance($a, $b, $c)
    {
        return new ClassSimpleXML11($a, $b, $c);
    }

    public function factoryMethod($a, $b, $c)
    {
        return new ClassSimpleXML11($a, $b, $c);
    }

    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

class ClassSimpleXML12
{
    public $value = 'not set';
    public $true = 'not set';
    public $false = 'not set';
    public $null = 'not set';
    public $eval = 'not set';
    public $ref = 'not set';
    public $innerBean = 'not set';
    public $array = 'not set';

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setTrue($value)
    {
        $this->true = $value;
    }

    public function setFalse($value)
    {
        $this->false = $value;
    }

    public function setNull($value)
    {
        $this->null = $value;
    }

    public function setEvaledCode($value)
    {
        $this->eval = $value;
    }

    public function setRef($value)
    {
        $this->ref = $value;
    }

    public function setInnerBean($value)
    {
        $this->innerBean = $value;
    }

    public function setArray($value)
    {
        $this->array = $value;
    }
}

class ChildBean
{
    public $someProperty;

    public function setSomeProperty($value)
    {
        $this->someProperty = $value;
    }
}