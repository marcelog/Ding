<?php
/**
 * This class will test the annotation driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Annotation
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
 * This class will test the annotation driver.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Aop.Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Annotation_AOP extends PHPUnit_Framework_TestCase
{
    private $_properties = array();

    public function setUp()
    {
        $this->_properties = array(
            'ding' => array(
                'log4php.properties' => RESOURCES_DIR . DIRECTORY_SEPARATOR . 'log4php.properties',
                'factory' => array(
                    'bdef' => array(
                        'annotation' => array('scanDir' => array(realpath(__DIR__))),
                        'xml' => array(
                        	'filename' => 'aop-annotation-simple.xml', 'directories' => array(RESOURCES_DIR)
                        )
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function can_intercept_method()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('methodIntercepted');
        var_dump($bean->targetMethod('aSd'));
        $this->assertEquals($bean->targetMethod('aSd'), 'BEFOREmethodReturnForaSdAFTER');
    }

    /**
     * @test
     */
    public function can_intercept_exception()
    {
        $container = ContainerImpl::getInstance($this->_properties);
        $bean = $container->getBean('exceptionIntercepted');
        $this->assertEquals($bean->thisWillThrowAnException('aSd'), 'aSd');
    }
}

/**
 * @Aspect
 */
class MyAspect
{
    /**
     * @MethodInterceptor(class-expression=ClassSimpleAOPAnnotation?,expression=^.+)
     */
	public function methodInterceptor(MethodInvocation $invocation)
	{
        return 'BEFORE' . $invocation->proceed() . 'AFTER';
	}

    /**
     * @ExceptionInterceptor(class-expression=ClassSimpleAOPAnnotation?,expression=^.+)
     */
	public function exceptionInterceptor(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments();
        return $args[0];
    }
}

class ClassSimpleAOPAnnotation1
{
    public function targetMethod($a)
    {
        return 'methodReturnFor' . $a;
    }
}

class ClassSimpleAOPAnnotation2
{
    public function thisWillThrowAnException()
    {
        throw new Exception('too bad!');
    }
}