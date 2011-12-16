<?php
/**
 * This class will test the YAML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Yaml
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
 * This class will test the YAML driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Yaml
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_YAML_IoC extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                'cache' => array(),
        		'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'ioc-yaml-simple.yaml', 'directories' => array(RESOURCES_DIR)
                        ),
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
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_read_beans_file()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'ioc-yaml-inexistant.yaml', 'directories' => array('neverland')
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
    public function cannot_invalid_scope()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $container->getBean('invalidScopeBean');
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
    public function cannot_parse_beans_file()
    {
        $properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'ioc-yaml-invalid.yaml', 'directories' => array(RESOURCES_DIR)
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
        $this->assertTrue($bean->ref instanceof ClassSimpleYAML);
        $this->assertTrue($bean->innerBean instanceof ClassSimpleYAML);
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleYAML);
        $this->assertTrue($bean->array['key7'] instanceof ClassSimpleYAML);
        $this->assertTrue(is_array($bean->array['key8']));
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
    public function can_setter_args()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleSetArgBean');
        $this->assertEquals($bean->value, '1a$');
        $this->assertTrue($bean->true);
        $this->assertFalse($bean->false);
        $this->assertNull($bean->null);
        $this->assertEquals($bean->eval, 'evaled code');
        $this->assertTrue($bean->ref instanceof ClassSimpleYAML);
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleYAML);
        $this->assertTrue($bean->array['key7'] instanceof ClassSimpleYAML);
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

        $this->assertTrue($bean instanceof ClassSimpleYAML);
        $this->assertTrue($bean2 instanceof ClassSimpleYAML);

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

        $this->assertTrue($bean instanceof ClassSimpleYAML);
        $this->assertTrue($bean2 instanceof ClassSimpleYAML);

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
        $this->assertTrue($bean instanceof ClassSimpleYAML5);
        $this->assertTrue($bean->dependency instanceof ClassSimpleYAML);
    }

    /**
     * @test
     */
    public function can_depends_on()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleDependsOn');
        $this->assertTrue($bean instanceof ClassSimpleYAML6);
        $this->assertTrue(ClassSimpleYAML7::$value);
        $this->assertTrue(ClassSimpleYAML8::$value);
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
                        'yaml' => array(
                        	'filename' => array('ioc-yaml-simple.yaml', 'moreBeans.yaml'),
                        	'directories' => array(RESOURCES_DIR, RESOURCES_DIR . DIRECTORY_SEPARATOR . 'moreBeans')
                        )
                    )
                )
            )
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('aSecretBean');
        $this->assertTrue($bean instanceof ClassSimpleYAML9);
        $this->assertTrue($bean->value instanceof ClassSimpleYAML);
    }

    /**
     * @test
     */
    public function can_method_lookup()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleMethodLookupBean');
        $this->assertTrue($bean instanceof ClassSimpleYAML10);
        $this->assertTrue($bean->createAnotherBean() instanceof ClassSimpleYAML);
    }

    /**
     * @test
     */
    public function can_factory_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aSimpleBeanFactory');
        $this->assertTrue($bean instanceof ClassSimpleYAML11);
        $this->assertEquals($bean->a, 'value1');
        $this->assertEquals($bean->b, 'value2');
        $this->assertEquals($bean->c, 'value3');
        $bean = $container->getBean('aSimpleBeanFactoryNoArgs');
        $this->assertTrue($bean instanceof ClassSimpleYAML11);
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
        $this->assertTrue($bean instanceof ClassSimpleYAML11);
        $this->assertEquals($bean->a, 'value1');
        $this->assertEquals($bean->b, 'value2');
        $this->assertEquals($bean->c, 'value3');
        $bean = $container->getBean('aSimpleBeanFactoryFromOtherBeanNoArgs');
        $this->assertTrue($bean instanceof ClassSimpleYAML11);
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

        $this->assertTrue($bean instanceof ClassSimpleYAML3);
        $this->assertTrue($bean2 instanceof ClassSimpleYAML12);
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
        $this->assertTrue($bean instanceof ChildBeanYaml);
    }
    /**
     * @test
     */
    public function can_get_aliased_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('someAliasedName');
        $this->assertTrue($bean instanceof ClassSimpleYAML12);
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

class ClassSimpleYAML
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
class ClassSimpleYAML2
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

class ClassSimpleYAML3
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

class ClassSimpleYAML4
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

class ClassSimpleYAML5
{
    public $dependency;

    public function __construct($value)
    {
        $this->dependency = $value;
    }
}

class ClassSimpleYAML6
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleYAML7
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleYAML8
{
    public static $value = null;

    public function __construct()
    {
        self::$value = true;
    }
}

class ClassSimpleYAML9
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

class ClassSimpleYAML10
{
    public function createAnotherBean()
    {

    }

    public function __construct()
    {
    }
}

class ClassSimpleYAML11
{
    public $a;
    public $b;
    public $c;

    public static function getInstanceNoArgs()
    {
        return new ClassSimpleYAML11(1, 2, 3);
    }

    public function factoryMethodNoArgs()
    {
        return new ClassSimpleYAML11(1, 2, 3);
    }

    public static function getInstance($a, $b, $c)
    {
        return new ClassSimpleYAML11($a, $b, $c);
    }

    public function factoryMethod($a, $b, $c)
    {
        return new ClassSimpleYAML11($a, $b, $c);
    }

    public function __construct($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }
}

class ClassSimpleYAML12
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

class ChildBeanYaml
{
    public $someProperty;

    public function setSomeProperty($value)
    {
        $this->someProperty = $value;
    }
}