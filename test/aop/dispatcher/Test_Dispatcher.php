<?php
/**
 * This class will test the Dispatcher.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Dispatcher
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
use Ding\Aspect\MethodInvocation;

/**
 * This class will test the Dispatcher.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Dispatcher
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Dispatcher extends PHPUnit_Framework_TestCase
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
                        	'filename' => 'aop-dispatcher.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_properly_set_method_invocation()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodInvocationTest');
        $invocation = $bean->methodInvocation('1', '2', '3');
        $oriInvocation = $invocation->getOriginalInvocation();
        $this->assertEquals($invocation->getClass(), 'ClassSimpleAOPDispatcherAspect3');
        $args = $invocation->getArguments();
        $this->assertEquals($args[0]->getClass(), 'ClassSimpleAOPMethodInvocationTest');
        $this->assertEquals($oriInvocation->getClass(), 'ClassSimpleAOPMethodInvocationTest');
        $this->assertEquals($oriInvocation->getArguments(), array('1', '2', '3'));
    }

    /**
     * @test
     * @expectedException Exception
     */
    public function can_throw_exception_when_no_exception_interceptors()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('throwsUnaspectedException');
        $bean->thisWillThrowAnException();
    }
}

class ClassSimpleAOPDispatcherAspect
{
    public function invoke(MethodInvocation $invocation)
    {
        return 'BEFORE' . $invocation->proceed() . 'AFTER';
    }
}


class ClassSimpleAOPDispatcher
{
    public function thisWillThrowAnException()
    {
        throw new Exception('too bad!');
    }
}

class ClassSimpleAOPDispatcherAspect2
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation;
    }
}

class ClassSimpleAOPDispatcherAspect3
{
    public function invoke(MethodInvocation $invocation)
    {
        return $invocation->proceed();
    }
}

class ClassSimpleAOPMethodInvocationTest
{
    public function methodInvocation()
    {
        return;
    }
}
