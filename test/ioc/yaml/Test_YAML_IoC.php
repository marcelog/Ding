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
                    'bdef' => array(
                        'yaml' => array(
                        	'filename' => 'ioc-yaml-simple.yaml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
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
        $this->assertTrue(is_array($bean->array));
        $this->assertEquals($bean->array['key1'], '1a$');
        $this->assertTrue($bean->array['key2']);
        $this->assertFalse($bean->array['key3']);
        $this->assertNull($bean->array['key4']);
        $this->assertEquals($bean->array['key5'], 'evaled code');
        $this->assertTrue($bean->array['key6'] instanceof ClassSimpleYAML);
        $this->assertTrue(is_array($bean->array['key7']));
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
        $this->assertTrue(is_array($bean->array['key7']));
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
    public $array = 'not set';

    public function __construct($value, $true, $false, $null, $eval, $ref, $array)
    {
        $this->value = $value;
        $this->true = $true;
        $this->false = $false;
        $this->null = $null;
        $this->eval = $eval;
        $this->ref = $ref;
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