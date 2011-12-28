<?php
/**
 * This class will test the annotation driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Annotation
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
 * This class will test the annotation driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Ioc.Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Annotation_IoC extends PHPUnit_Framework_TestCase
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
                    ),
                    'properties' => array('aProperty' => 'aValue')
                )
            )
        );
    }

    /**
     * @test
     * For issue #104
     */
    public function can_get_class_with_underscore()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBeanFromUnderscoreClass');
        $this->assertTrue($bean instanceof Some_UnderScore_Class);
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
    /**
     * @test
     */
    public function can_get_component()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('myComponentBean');
        $this->assertTrue($bean instanceof MyComponentBean);
        $this->assertTrue($bean->myComponentDependency instanceof MyComponentDependency);
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
    public function can_inherit_from_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('childBean');
        $this->assertTrue($bean->someProperty instanceof MyComponentDependency);
    }

    /**
     * @test
     */
    public function can_get_component_aliased()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('anAliasForABean');
        $this->assertTrue($bean instanceof MyComponentDependency);
    }
    /**
     * @test
     */
    public function can_get_bean_aliased()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aliasedBean');
        $this->assertTrue($bean instanceof ClassSimpleAnnotation);
    }
    /**
     * @test
     */
    public function can_get_by_class()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBeanDefinitionByClass('aliasedBean');
    }

    /**
     * @test
     */
    public function can_at_value_property()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aValueAnnotationClass');
        $this->assertEquals($bean->getMyValue(), "aValue/somethingelse");
    }

    /**
     * @test
     */
    public function can_at_value_constructor_args_in_at_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBeanWithConstructorArgs');
        $this->assertEquals($bean->a, 'value1');
        $this->assertEquals($bean->b, 'value2');
    }
    /**
     * @test
     */
    public function can_set_constructor_arguments_by_name()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBeanWithConstructorArgumentNames');
        $this->assertEquals($bean->arg1, 'value1');
        $this->assertEquals($bean->arg2, 'value2');
        $this->assertEquals($bean->arg3, 'value3');
    }
    /**
     * @test
     */
    public function can_set_constructor_arguments_by_name_in_at_configuration()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aBeanWithConstructorArgumentsInAtConf');
        $this->assertEquals($bean->arg1, 'value1');
        $this->assertEquals($bean->arg2, 'value2');
        $this->assertEquals($bean->arg3, 'value3');
    }
}

/**
 * @InitMethod(method=initMethod)
 * @DestroyMethod(method=destroyMethod)
 */
class ASimpleDestroyInitClass
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

class Some_UnderScore_Class
{

}
/**
 * @Configuration
 */
class ClassSimpleAnnotationConfiguration
{
    /**
     * @Bean(class=ASimpleDestroyInitClass)
     * @Scope(value=singleton)
     */
    public function aSimpleInitMethodClass()
    {
        return new ASimpleDestroyInitClass();
    }

    /**
     * @Bean(class=Some_UnderScore_Class)
     * @Scope(value=singleton)
     */
    public function aBeanFromUnderscoreClass()
    {
        return new Some_UnderScore_Class;
    }
    /**
     * @Bean(class=ASimpleDestroyInitClass)
     * @Scope(value=singleton)
     */
    public function aSimpleDestroyMethodClass()
    {
        return new ASimpleDestroyInitClass();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation)
     * @Scope(value=singleton)
     * @InitMethod(method=initMethod)
     */
    public function aSimpleInitMethodBean()
    {
        return new ClassSimpleAnnotation4();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation)
     * @Scope(value=singleton)
     * @DestroyMethod(method=destroyMethod)
     */
    public function aSimpleDestroyMethodBean()
    {
        return new ClassSimpleAnnotation4();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation)
     * @Scope(value=singleton)
     */
    public function aSimpleSingletonBean()
    {
        return new ClassSimpleAnnotation();
    }

    /**
     * @Bean(name={renamedBean,aliasedBean},class=ClassSimpleAnnotation)
     * @Scope(value=singleton)
     */
    public function whateverHere()
    {
        return new ClassSimpleAnnotation();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation3)
     * @Scope(value=singleton)
     */
    public function required()
    {
        return new ClassSimpleAnnotation3();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation5)
     * @Scope(value=singleton)
     */
    public function requiredFails()
    {
        return new ClassSimpleAnnotation5();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation2)
     * @Scope(value=singleton)
     */
    public function injectedBean()
    {
        return new ClassSimpleAnnotation2();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation)
     * @Scope(value=prototype)
     */
    public function aSimplePrototypeBean()
    {
        return new ClassSimpleAnnotation();
    }

    /**
     * @Bean(class=ClassSimpleAnnotation)
     * @Scope(value=invalid)
     */
    public function invalidScopeBean()
    {
        return new ClassSimpleAnnotation();
    }
    /**
     * @Bean
     * @Value(value="value1")
     * @Value(value="value2")
     */
    public function aBeanWithConstructorArgs($a, $b)
    {
        return new BeanWithConstructorArgs($a, $b);
    }
    /**
     * @Bean
     * @Value(name="arg1", value="value1")
     * @Value(name="arg2", value="value2")
     * @Value(name="arg3", value="value3")
     */
    public function aBeanWithConstructorArgumentsInAtConf($arg2, $arg3, $arg1)
    {
        return new ABeanAnnotatedWithConstructorArgumentNames($arg3, $arg1, $arg2);
    }
    public function __construct()
    {
    }
}

class BeanWithConstructorArgs
{
    public $a;
    public $b;

    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}
class ClassSimpleAnnotation
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

class ClassSimpleAnnotation2
{
    /**
     * @Resource
     */
    public $aSimplePrototypeBean = null;

    /**
     * @Resource
     */
    private $aSimpleSingletonBean = null;
    public $somethingElse = null;

    public function getASimpleSingletonBean()
    {
        return $this->aSimpleSingletonBean;
    }

    /**
     * @Resource
     */
    public function setASimpleInitMethodClass($value)
    {
        $this->somethingElse = $value;
    }

    public function __construct()
    {
    }
}

class ClassSimpleAnnotation4
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

class ClassSimpleAnnotation3
{
    public $value1;
    public $value2;

    /**
     * @Required
     * @Resource
     */
    public function setInjectedBean($value)
    {
        $this->value1 = $value;
    }
}

class ClassSimpleAnnotation5
{
    public $value2;

    /**
     * @Required
     */
    public function setThisWillFail($value)
    {
        $this->value2 = $value;
    }
}

/**
 * This is our bean.
 * @Component(name=myComponentBean)
 * @InitMethod(method=init)
 * @DestroyMethod(method=destroy)
 * @Scope(value=singleton)
 */
class MyComponentBean
{
    /**
     * @Resource
     */
    public $myComponentDependency;

    public function init()
    {
    }
    public function destroy()
    {
    }
    public function __construct()
    {

    }
}

/**
 * @Component(name={myComponentDependency,anAliasForABean})
 * @Scope(value=singleton)
 */
class MyComponentDependency
{

}

/**
 * @Component
 */
abstract class AParentBean
{
    public $someProperty;

    /** @Resource */
    public function setMyComponentDependency($value)
    {
        $this->someProperty = $value;
    }
}
/**
 * @Component(name={aParentBeanWithName,anAliasForTheParent})
 */
abstract class AnotherParentWithName extends AParentBean
{

}

/**
 * @Component(name=someUnneededParentNameBecauseThisIsAbstractButHelpsCoverage)
 */
abstract class AnotherParent extends AnotherParentWithName
{

}

abstract class UnnamedParent extends AnotherParent
{

}
/**
 * @Component(name=childBean)
 */
class ChildBeanAnnotated extends UnnamedParent
{

}

/**
 * @Component(name="aValueAnnotationClass")
 */
class AValueAnnotationClass
{
    /**
     * @Value(value="${aProperty}/somethingelse")
     */
    private $_myValue;

    public function getMyValue()
    {
        return $this->_myValue;
    }
}

/**
 * @Component(name="aBeanWithConstructorArgumentNames")
 */
class ABeanAnnotatedWithConstructorArgumentNames
{
    public $arg1;
    public $arg2;
    public $arg3;
    /**
     * @Value(name="arg1", value="value1")
     * @Value(name="arg2", value="value2")
     * @Value(name="arg3", value="value3")
     */
    public function __construct($arg3, $arg1, $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
        $this->arg3 = $arg3;
    }
}