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
    public function can_at_singleton()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('atSingleton');
        $bean2 = $container->getBean('atSingleton');

        $this->assertEquals($bean::$instances, 1);
    }

    /**
     * @test
     */
    public function can_at_prototype()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('atPrototype');
        $bean2 = $container->getBean('atPrototype');

        $this->assertEquals($bean::$instances, 2);
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
    public function can_at_postconstruct()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('postConstructBean');
        $this->assertTrue($bean->something);
    }

    /**
     * @test
     */
    public function can_at_predestroy()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('preDestroyBean');
         // XXX bad... unset() does not work because ContainerImpl is a singleton
         // and holds a reference to itself, so the destructor is never called.
        $container->__destruct();
        $this->assertNull($bean->something);
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
    public function can_at_resource_with_name()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('injectedBean');
        $this->assertTrue($bean->injectedWithName instanceof ClassSimpleAnnotation);
        $this->assertTrue($bean->injectedWithNameAndSetter instanceof ClassSimpleAnnotation);
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
        $beans = $container->getBeansByClass('ClassSimpleAnnotation');
        $this->assertEquals($beans, array(
        	'aSimpleInitMethodBean', 'aSimpleDestroyMethodBean',
        	'aSimpleSingletonBean', 'renamedBean', 'aSimplePrototypeBean',
        	'invalidScopeBean'
        ));
    }
    /**
     * @test
     */
    public function can_get_by_class_returns_empty_if_none_found()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $beans = $container->getBeansByClass('NotExistant');
        $this->assertEquals($beans, array());
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

    /**
     * @test
     */
    public function can_declare_beans_inside_components()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('beanDeclaredInMethod');
        $this->assertTrue($bean instanceof AClassForABeanFromAMethod);
    }

    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_properties_without_type()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject1');
    }

    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_properties_with_unknown_type()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject2');
    }

    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_non_array_properties_with_many_candidates()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject3');
    }

    /**
     * @test
     */
    public function can_ignore_non_required_at_inject_properties_if_cant_inject()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject4');
    }

    /**
     * @test
     */
    public function can_at_inject_arrays_on_multiple_candidates()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject5');
        $this->assertEquals(count($bean->property), 2);
        $this->assertTrue($bean->property[0] instanceof AnInjectCandidate);
        $this->assertTrue($bean->property[1] instanceof AnInjectCandidate2);
    }

    /**
     * @test
     */
    public function can_at_inject_single_candidate()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject6');
        $this->assertTrue($bean->property instanceof AnInjectCandidate3);
    }

    /**
     * @test
     */
    public function can_at_inject_single_candidate_in_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject7');
        $this->assertTrue($bean->property instanceof AnInjectCandidate3);
    }

    /**
     * @test
     */
    public function can_at_inject_arrays_on_multiple_candidates_in_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject8');
        $this->assertEquals(count($bean->property), 2);
        $this->assertTrue($bean->property[0] instanceof AnInjectCandidate);
        $this->assertTrue($bean->property[1] instanceof AnInjectCandidate2);
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_empty_arguments_in_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject9');
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_multiple_arguments_in_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject10');
    }

    /**
     * @test
     */
    public function can_at_inject_single_candidate_in_constructor_and_skip_nontyped_args()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject11');
        $this->assertTrue($bean->a instanceof InjectComponentsExtendThisSingle);
        $this->assertEquals($bean->b, "asd");
    }

    /**
     * @test
     */
    public function can_at_inject_single_candidate_in_constructor_and_skip_nontyped_args_in_bean_methods()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject11');
        $this->assertTrue($bean->a instanceof InjectComponentsExtendThisSingle);
        $this->assertEquals($bean->b, "asd");
    }

    /**
     * @test
     */
    public function can_at_inject_constructor_arguments_specifying_name_and_type()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject13');
        $this->assertEquals(count($bean->a), 2);
        $this->assertTrue($bean->a[0] instanceof InjectComponentsExtendThis);
        $this->assertTrue($bean->a[1] instanceof InjectComponentsExtendThis);
        $this->assertTrue($bean->b instanceof InjectComponentsExtendThisSingle);
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_constructors_with_name_and_no_type()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject14');
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_constructors_with_type_and_no_name()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject15');
    }
    /**
     * @test
     */
    public function can_at_named_components()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('aNamedComponent');
        $this->assertTrue($bean instanceof NamedComponent);
        $this->assertTrue($bean->property instanceof DependencyOfNamedComponent);
    }
    /**
     * @test
     */
    public function can_at_primary_components()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject16');
        $this->assertTrue($bean->property instanceof InjectPrimaryComponent);
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_multiple_primary_candidates()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject17');
    }
    /**
     * @test
     */
    public function can_at_bean_methods()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject18');
        $this->assertTrue($bean->property instanceof ASimpleDestroyInitClass);
    }

    /**
     * @test
     */
    public function can_define_primary_beans_in_xml()
    {
        $properties = $this->_properties;
        $properties['ding']['factory']['bdef']['xml'] = array(
        	'filename' => 'inject.xml', 'directories' => array(RESOURCES_DIR)
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('inject19');
        $this->assertTrue($bean->property instanceof AnXmlInjectCandidate);
    }

    /**
     * @test
     */
    public function can_define_primary_beans_in_yaml()
    {
        $properties = $this->_properties;
        $properties['ding']['factory']['bdef']['yaml'] = array(
        	'filename' => 'inject.yaml', 'directories' => array(RESOURCES_DIR)
        );
        $container = ContainerImpl::getInstance($properties);
        $bean = $container->getBean('inject19');
        $this->assertTrue($bean->property instanceof AnXmlInjectCandidate);
    }

    /**
     * @test
     */
    public function can_trigger_events_without_listeners()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $container->eventDispatch('inexistantEvent', 'blah');
    }

    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_multiple_primary_candidates_without_complete_name()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject20');
    }
    /**
     * @test
     * @expectedException \Ding\Bean\Factory\Exception\InjectByTypeException
     */
    public function cannot_at_inject_multiple_primary_candidates_with_inexistant_named_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject21');
    }
    /**
     * @test
     */
    public function can_at_inject_and_choose_with_at_named_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject22');
        $this->assertTrue($bean->property instanceof AnInjectCandidate);
    }
    /**
     * @test
     */
    public function can_at_inject_methods_and_choose_with_at_named_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject23');
        $this->assertTrue($bean->property instanceof AnInjectCandidate);
    }
    /**
     * @test
     */
    public function can_at_inject_constructor_and_choose_with_at_named_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject24');
        $this->assertTrue($bean->property instanceof AnInjectCandidate);
    }
    /**
     * @test
     */
    public function can_at_inject_specific_type_and_name_in_constructor_and_choose_with_at_named_bean()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('inject25');
        $this->assertTrue($bean->property instanceof AnInjectCandidate);
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
     * @Bean(class=ASimpleDestroyInitClass, primary="true")
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

    /**
     * @Inject
     * @Bean
     * @Value(name="b", value="asd")
     */
    public function inject12(InjectComponentsExtendThisSingle $a, $b)
    {
        return new InjectProperty11($a, $b);
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
     * @Resource(name="aSimplePrototypeBean")
     */
    public $injectedWithName = null;
    public $injectedWithNameAndSetter = null;

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

    /**
     * @Resource(name="aSimplePrototypeBean")
     */
    public function setAnotherStuff($value)
    {
        $this->injectedWithNameAndSetter = $value;
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


class AClassForABeanFromAMethod
{
}

/**
 * @Component
 * @Scope(value=singleton)
 */
class AComponentConfigurationClass
{
    /**
     * @Bean
     */
    public function beanDeclaredInMethod()
    {
        return new AClassForABeanFromAMethod();
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

/**
 * @Component(name="atSingleton")
 * @Singleton
 */
class ClassSingletonAnnotated
{
    public static $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }
}

/**
 * @Component(name="atPrototype")
 * @Prototype
 */
class ClassPrototypeAnnotated
{
    public static $instances = 0;

    public function __construct()
    {
        self::$instances++;
    }
}

/**
 * @Component(name="postConstructBean")
 */
class AnnotatedWithPostConstruct
{
    public $something = false;

    /**
     * @PostConstruct
     */
    public function something()
    {
        $this->something = true;
    }
}

/**
 * @Component(name="preDestroyBean")
 */
class AnnotatedWithPreDestroy
{
    public $something = true;

    /**
     * @PreDestroy
     */
    public function something()
    {
        $this->something = null;
    }
}

interface InjectComponentsExtendThis
{

}

interface InjectComponentsExtendThisSingle
{

}

/**
 * @Component(name="chooseThisWithNamed")
 */
class AnInjectCandidate implements InjectComponentsExtendThis
{
}

/**
 * @Component
 */
class AnInjectCandidate2 implements InjectComponentsExtendThis
{
}

/**
 * @Component
 */
class AnInjectCandidate3 implements InjectComponentsExtendThisSingle
{
}

/**
 * @Component(name="inject1")
 */
class InjectProperty1
{
    /**
     * @Inject
     */
    protected $property;
}

/**
 * @Component(name="inject2")
 */
class InjectProperty2
{
    /**
     * @Inject(type="UnexistantType")
     */
    protected $property;
}

/**
 * @Component(name="inject3")
 */
class InjectProperty3
{
    /**
     * @Inject(type="InjectComponentsExtendThis")
     */
    protected $property;
}

/**
 * @Component(name="inject4")
 */
class InjectProperty4
{
    /**
     * @Inject(type="UnexistantType", required="false")
     */
    protected $property;
}

/**
 * @Component(name="inject5")
 */
class InjectProperty5
{
    /**
     * @Inject(type="InjectComponentsExtendThis[]")
     */
    public $property;
}

/**
 * @Component(name="inject6")
 */
class InjectProperty6
{
    /**
     * @Inject(type="InjectComponentsExtendThisSingle")
     */
    public $property;
}

/**
 * @Component(name="inject7")
 */
class InjectProperty7
{
    public $property;

    /**
     * @Inject
     */
    public function injected(InjectComponentsExtendThisSingle $a)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject8")
 */
class InjectProperty8
{
    public $property;

    /**
     * @Inject(type="InjectComponentsExtendThis[]")
     */
    public function injected(array $a)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject9")
 */
class InjectProperty9
{
    public $property;

    /**
     * @Inject(type="InjectComponentsExtendThis[]")
     */
    public function injected()
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject10")
 */
class InjectProperty10
{
    public $property;

    /**
     * @Inject(type="InjectComponentsExtendThis")
     */
    public function injected($a, $b)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject11")
 */
class InjectProperty11
{
    public $a;
    public $b;

    /**
     * @Inject
     * @Value(name="b", value="asd")
     */
    public function __construct(InjectComponentsExtendThisSingle $a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}

/**
 * @Component(name="inject13")
 */
class InjectProperty13
{
    public $a;
    public $b;

    /**
     * @Inject(name="a", type="InjectComponentsExtendThis[]")
     * @Inject(name="b", type="InjectComponentsExtendThisSingle")
     */
    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}

/**
 * @Component(name="inject14")
 */
class InjectProperty14
{
    public $a;

    /**
     * @Inject(name="a")
     */
    public function __construct($a)
    {
        $this->a = $a;
    }
}

/**
 * @Component(name="inject15")
 */
class InjectProperty15
{
    public $a;

    /**
     * @Inject(type="a")
     */
    public function __construct($a)
    {
        $this->a = $a;
    }
}

/**
 * @Named
 */
class DependencyOfNamedComponent
{
}
/**
 * @Named(name="aNamedComponent")
 */
class NamedComponent
{
    /**
     * @Inject(type="DependencyOfNamedComponent")
     */
    public $property;
}

interface InjectComponentsWithPrimaryExtendThis
{

}

/**
 * @Component
 * @Primary
 */
class InjectPrimaryComponent implements InjectComponentsWithPrimaryExtendThis
{

}
/**
 * @Component
 */
class InjectNonPrimaryComponent implements InjectComponentsWithPrimaryExtendThis
{

}

/**
 * @Component(name="inject16")
 */
class InjectProperty16
{
    public $property;

    /**
     * @Inject(type="InjectComponentsWithPrimaryExtendThis")
     */
    public function injected($a)
    {
        $this->property = $a;
    }
}

interface MultipleInjectComponentsWithPrimaryExtendThis
{

}

/**
 * @Component
 * @Primary
 */
class InjectPrimaryComponent2 implements MultipleInjectComponentsWithPrimaryExtendThis
{

}

/**
 * @Component
 * @Primary
 */
class InjectPrimaryComponent3 implements MultipleInjectComponentsWithPrimaryExtendThis
{

}

/**
 * @Component(name="inject17")
 */
class InjectProperty17
{
    public $property;

    /**
     * @Inject(type="MultipleInjectComponentsWithPrimaryExtendThis")
     */
    public function injected($a)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject18")
 */
class InjectProperty18
{
    /**
	 * @Inject(type="ASimpleDestroyInitClass")
     */
    public $property;
}

interface MultipleXmlInjectComponentsWithPrimaryExtendThis
{

}

class AnXmlInjectCandidate implements MultipleXmlInjectComponentsWithPrimaryExtendThis
{

}

class AnXmlInjectCandidate2 implements MultipleXmlInjectComponentsWithPrimaryExtendThis
{

}
/**
 * @Component(name="inject19")
 */
class InjectProperty19
{
    /**
	 * @Inject(type="MultipleXmlInjectComponentsWithPrimaryExtendThis")
     */
    public $property;
}

/**
 * @Component(name="inject20")
 */
class InjectProperty20
{
    /**
	 * @Inject(type="InjectComponentsExtendThis")
	 * @Named
     */
    public $property;
}

/**
 * @Component(name="inject21")
 */
class InjectProperty21
{
    /**
	 * @Inject(type="InjectComponentsExtendThis")
	 * @Named(name="inexistantBean")
     */
    public $property;
}

/**
 * @Component(name="inject22")
 */
class InjectProperty22
{
    /**
	 * @Inject(type="InjectComponentsExtendThis")
	 * @Named(name="chooseThisWithNamed")
     */
    public $property;
}

/**
 * @Component(name="inject23")
 */
class InjectProperty23
{
    public $property;

    /**
     * @Inject(type="InjectComponentsExtendThis")
     * @Named(name="chooseThisWithNamed")
     */
    public function injected($a)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject24")
 */
class InjectProperty24
{
    public $property;

    /**
     * @Inject
     * @Named(name="chooseThisWithNamed", arg="a")
     */
    public function __construct(InjectComponentsExtendThis $a)
    {
        $this->property = $a;
    }
}

/**
 * @Component(name="inject25")
 */
class InjectProperty25
{
    public $property;

    /**
     * @Inject(name="a", type="InjectComponentsExtendThis")
     * @Named(name="chooseThisWithNamed", arg="a")
     */
    public function __construct($a)
    {
        $this->property = $a;
    }
}
