<?php
/**
 * This class will test the XML aop driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Xml
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
use Ding\Aspect\MethodInvocation;

/**
 * This class will test the XML aop driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Xml
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_XML_AOP extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'xml' => array(
                        	'filename' => 'aop-xml-simple.xml', 'directories' => array(RESOURCES_DIR)
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
     */
    public function can_intercept_exception_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('exceptionIntercepted');
        $this->assertEquals($bean->thisWillThrowAnException('aSd'), 'aSd');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function can_intercept_and_proceed_exception_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('exceptionInterceptedAndProceed');
        $bean->thisWillThrowAnException();
    }

    /**
     * @test
     * @expectedException Ding\Bean\Factory\Exception\BeanFactoryException
     */
    public function cannot_invalid_aspect_type()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('invalidAspectType');
    }

    /**
     * @test
     */
    public function can_intercept_method_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodIntercepted');
        $this->assertEquals($bean->targetMethod('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_intercept_method_from_parent_class()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodInterceptedSubClass');
        $this->assertEquals($bean->targetMethod('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }
    /**
     * @test
     */
    public function can_intercept_multiple_methods_from_bean_aop()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('multipleMethodIntercepted');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_define_global_aspects()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('globalAspectedBeanA');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $bean = $container->getBean('globalAspectedBeanB');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_define_global_pointcuts()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('globalPointcut');
        $this->assertEquals($bean->getSomething('aSd'), 'BEFOREmethodReturnForaSdAFTER');
        $this->assertEquals($bean->getSomethingElse('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }
}

class ClassSimpleAOPXML
{
    public function targetMethod($a)
    {
        return 'methodReturnFor' . $a;
    }
}
class ClassSimpleAOPXMLAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        return 'BEFORE' . $invocation->proceed() . 'AFTER';
    }
}

class ClassSimpleAOPXML2
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPXMLGlobalSomething
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPXMLGlobalSomethingElse
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPXML3
{
    public function getSomething($a)
    {
        return 'methodReturnFor' . $a;
    }
    public function getSomethingElse($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPExceptionXML
{
    public function thisWillThrowAnException()
    {
        throw new Exception('too bad!');
    }
}

class ClassSimpleAOPExceptionXMLAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        return $args[0];
    }
}

class ClassSimpleAOPExceptionAndProceedXML
{
    public function thisWillThrowAnException()
    {
        throw new InvalidArgumentException('too bad!');
    }
}

class ClassSimpleAOPExceptionAndProceedXMLAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}

class AspectedParent extends ClassSimpleAOPXML
{

}
class AChildOfAnAspectedClass extends AspectedParent
{

}